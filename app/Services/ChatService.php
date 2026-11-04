<?php

namespace App\Services;

use App\Enums\ChatRole;
use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use App\Models\Setting;
use App\Models\UnansweredQuestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Orchestrates a single chatbot turn:
 *   1. Resolve or create the session (authed user or guest token).
 *   2. Detect the language of the user's message.
 *   3. Retrieve relevant KB articles (RAG).
 *   4. Call Groq with the passages and recent history.
 *   5. Persist both messages, update session stats.
 *   6. Log knowledge gaps without treating provider failures as missing data.
 */
class ChatService
{
    public function __construct(
        private readonly KnowledgeRetriever $retriever,
        private readonly TourismDataRetriever $tourismData,
        private readonly GroqService $groq,
    ) {}

    /**
     * Handle a visitor message and return the assistant's reply.
     *
     * @return array{
     *     reply: string,
     *     html: string,
     *     lang: string,
     *     grounded: bool,
     *     error: ?string,
     *     session_id: int,
     *     message_id: int,
     * }
     */
    public function handle(string $text, ?string $requestedLang = null): array
    {
        $text = trim($text);
        if ($text === '') {
            throw new \InvalidArgumentException('Message cannot be empty.');
        }

        $session = $this->resolveSession();
        $detector = app(LanguageDetector::class);

        $userPreference = $session->user?->preferred_lang;
        $lang = $requestedLang && in_array($requestedLang, ['en', 'fil'], true)
            ? $requestedLang
            : $detector->detectWithPreference($text, $userPreference);

        // Persist the user's message first so nothing is lost if Groq fails.
        $userMessage = $session->messages()->create([
            'role' => ChatRole::User,
            'content' => $text,
            'lang' => $lang,
        ]);

        $history = $this->recentHistory($session, $userMessage->id);
        $retrievalQuery = $this->contextualQuery($text, $history);

        // ── RAG retrieval ──
        $kb = $this->retriever->retrieve(
            $retrievalQuery,
            (int) Setting::get('max_kb', config('groq.max_kb')),
            verifiedOnly: true,
            includeLocalDirectory: true,
        );
        $live = $this->tourismData->retrieve(
            $retrievalQuery,
            (int) Setting::get('max_db', config('groq.max_db', 6)),
        );
        $context = array_merge($kb, $live);
        $kbIds = collect($context)->pluck('id')->all();

        $verifiedReply = $this->answerFromVerifiedArticles($text, $retrievalQuery, $context, $lang);

        if ($verifiedReply !== null) {
            $result = [
                'reply' => $verifiedReply,
                'tokens' => null,
                'latency_ms' => 0,
                'grounded' => true,
            ];
        } else {
            $result = $this->askGroq($text, $context, $lang, $history);
        }

        $reply = $result['reply'] !== '' ? $result['reply'] : $this->fallback($lang);

        // Keep provider outages distinct from tourism questions with no answer.
        // A general-knowledge answer without local passages is still a real answer.
        $isServiceError = ! empty($result['error']);
        $isFallback = ! $isServiceError && $this->looksLikeFallback($reply);

        $assistantMessage = $session->messages()->create([
            'role' => ChatRole::Assistant,
            'content' => $reply,
            'lang' => $lang,
            'retrieved_kb_ids' => $kbIds,
            'tokens_used' => $result['tokens'],
            'latency_ms' => $result['latency_ms'],
        ]);

        $session->touchActive();

        if ($isFallback) {
            $this->logUnanswered($session, $text, $lang);
        }

        return [
            'reply' => $reply,
            'html' => $this->markdownToHtml($reply),
            'lang' => $lang,
            'grounded' => ! $isServiceError && ! $isFallback && (bool) ($result['grounded'] ?? false),
            'error' => $isServiceError ? $reply : null,
            'session_id' => $session->id,
            'message_id' => $assistantMessage->id,
        ];
    }

    /** Attach a "helpful / not helpful" rating to an assistant message. */
    public function recordFeedback(int $messageId, string $feedback): bool
    {
        if (! in_array($feedback, ['helpful', 'not_helpful'], true)) {
            return false;
        }

        $message = ChatbotMessage::find($messageId);
        if (! $message) {
            return false;
        }

        $message->update(['feedback' => $feedback]);

        return true;
    }

    /** Recent turns formatted for Groq's chat-completions API. */
    private function recentHistory(ChatbotSession $session, int $currentMessageId): array
    {
        $turns = config('groq.history_turns');

        $messages = $session->messages()
            ->where('id', '!=', $currentMessageId)
            ->orderByDesc('id')
            ->limit($turns * 2)
            ->get()
            ->reverse()
            ->values();

        return $messages
            ->filter(function (ChatbotMessage $message, int $index) use ($messages) {
                if ($message->role === ChatRole::Assistant) {
                    return ! $this->looksLikeFallback($message->content)
                        && ! $this->isServiceError($message->content);
                }

                $nextMessage = $messages->get($index + 1);

                return ! ($nextMessage?->role === ChatRole::Assistant
                    && ($this->looksLikeFallback($nextMessage->content)
                        || $this->isServiceError($nextMessage->content)));
            })
            ->map(function (ChatbotMessage $m) {
                return [
                    'role' => $m->role === ChatRole::User ? 'user' : 'model',
                    'text' => $m->content,
                ];
            })
            ->all();
    }

    /**
     * Resolve short follow-ups ("Magkano?", "How much is it?") against the
     * previous visitor turn so retrieval keeps the topic the visitor expects.
     */
    private function contextualQuery(string $text, array $history): string
    {
        $priorQuestions = collect($history)
            ->where('role', 'user')
            ->pluck('text')
            ->take(-2)
            ->implode(' ');

        if ($priorQuestions === '') {
            return $text;
        }

        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        $lowerText = mb_strtolower($text);
        $followUpMarkers = [
            'it', 'its', 'that', 'this', 'these', 'those', 'there', 'they', 'them',
            'yun', 'yung', 'iyan', 'iyon', 'ito', 'doon', 'diyan', 'dito', 'siya', 'niya',
            'sila', 'nila', 'magkano', 'presyo',
        ];
        $followUpPhrases = [
            'what about', 'how about', 'what else', 'ano pa', 'meron ba', 'pwede ba',
            'is it', 'are they', 'does it', 'do they', 'can i', 'puwede ko',
        ];

        $hasMarker = collect($words)
            ->map(fn (string $word) => mb_strtolower(preg_replace('/[^\pL\pN]+/u', '', $word)))
            ->contains(fn (string $word) => in_array($word, $followUpMarkers, true))
            || collect($followUpPhrases)->contains(fn (string $phrase) => str_contains($lowerText, $phrase));

        if ($wordCount <= 2 || ($wordCount <= 7 && $hasMarker)) {
            return trim($priorQuestions.' '.$text);
        }

        return $text;
    }

    /**
     * Call Groq and return a localized, actionable message on provider errors.
     */
    private function askGroq(string $question, array $kb, string $lang, array $history): array
    {
        if (! $this->groq->isConfigured()) {
            $message = $lang === 'fil'
                ? 'Hindi pa naka-configure ang Groq AI. Pakisuri ang GROQ_API_KEY sa server .env.'
                : 'Groq AI is not configured. Please check GROQ_API_KEY in the server .env.';

            return [
                'reply' => $message,
                'tokens' => null,
                'latency_ms' => 0,
                'grounded' => false,
                'error' => true,
            ];
        }

        try {
            return $this->groq->ask($question, $kb, $lang, $history);
        } catch (\Throwable $e) {
            Log::warning('Groq call failed', [
                'message' => Str::limit($e->getMessage(), 300),
                'code' => $e->getCode(),
            ]);
            $message = $this->groq->userFacingError($e, $lang);

            return [
                'reply' => $message,
                'tokens' => null,
                'latency_ms' => 0,
                'grounded' => false,
                'error' => true,
            ];
        }
    }

    private function isServiceError(string $reply): bool
    {
        return Str::startsWith($reply, [
            'Groq AI is not configured.',
            'Groq rejected the API key.',
            'The configured Groq model is unavailable.',
            'The Groq rate limit or quota has been reached.',
            'Groq could not process this request.',
            'Groq AI is temporarily unavailable.',
            'Could not connect to the Groq AI service.',
            'Hindi pa naka-configure ang Groq AI.',
            'Hindi tinanggap ng Groq ang API key.',
            'Hindi available ang Groq model',
            'Naabot na ang limit o quota ng Groq.',
            'Hindi maproseso ng Groq',
            'Pansamantalang hindi available ang Groq AI.',
            'Hindi makakonekta sa Groq AI',
        ]);
    }

    private function answerFromVerifiedArticles(string $question, string $lookup, array $articles, string $lang): ?string
    {
        $articles = collect($articles)
            ->sortBy(fn (array $article) => str_starts_with($article['source'] ?? '', TourismDataRetriever::SOURCE) ? 0 : 1)
            ->values()
            ->all();

        $portalArticles = array_values(array_filter(
            $articles,
            fn (array $article) => str_starts_with($article['source'] ?? '', 'https://www.travelorientalmindoro.ph/'),
        ));

        $isRecommendation = $this->isRecommendationRequest($question);

        if ($isRecommendation && preg_match('/\b(beach|beaches|dagat|baybayin)\b/iu', $lookup)) {
            $beaches = collect($articles)
                ->filter(fn (array $article) => preg_match('/beach|dagat/i', ($article['title'] ?? '').' '.($article['category'] ?? '')) === 1)
                ->unique(fn (array $article) => mb_strtolower($this->cleanListingTitle($article['title'])))
                ->values();

            if ($beaches->isEmpty()) {
                return null;
            }

            $lines = $beaches->take(3)->map(function (array $article) {
                $name = $this->cleanListingTitle($article['title']);
                preg_match('/^Address:\s*(.+)$/mi', $article['content'], $addressMatch);
                preg_match('/^Email:\s*(.+)$/mi', $article['content'], $emailMatch);
                $address = trim($addressMatch[1] ?? '');
                $email = trim($emailMatch[1] ?? '');
                $details = $address !== '' && mb_strtolower($address) !== 'null'
                    ? $address
                    : 'address not listed';

                if ($email !== '' && mb_strtolower($email) !== 'null') {
                    $details .= '; contact: '.$email;
                }

                return '- **'.$name.'** — '.$details;
            });

            $intro = $lang === 'fil'
                ? 'Ito ang beach-related listing na nakita sa verified tourism data:'
                : 'This is the beach-related listing found in the verified tourism data:';
            $caveat = $lang === 'fil'
                ? 'Hindi sapat ang nakalistang detalye para kumpirmahin ang eksaktong lokasyon o ikumpara kung alin ang pinakamaganda.'
                : 'The listing does not provide enough detail to confirm the exact location or rank it as the best.';

            return $intro."\n".$lines->implode("\n")."\n\n".$caveat;
        }

        $asksAccommodationPrice = preg_match('/\b(hotel|hotels|resort|resorts|accommodation|lodging|tuluyan|matutuluyan)\b/iu', $lookup)
            && preg_match('/\b(price|prices|rate|rates|cost|fee|presyo|magkano|bayad|gastos)\b/iu', $lookup);

        if ($asksAccommodationPrice) {
            $accommodations = collect($articles)
                ->filter(fn (array $article) => preg_match('/hotel|resort|accommodation|lodging/i', ($article['category'] ?? '').' '.($article['title'] ?? '')) === 1)
                ->unique(fn (array $article) => mb_strtolower($this->cleanListingTitle($article['title'])))
                ->take(5);

            if ($accommodations->isEmpty()) {
                return null;
            }

            $hasRate = false;
            $lines = $accommodations->map(function (array $article) use (&$hasRate) {
                $name = $this->cleanListingTitle($article['title']);
                preg_match('/^Address:\s*(.+)$/mi', $article['content'], $addressMatch);
                preg_match('/^Nightly rate:\s*(.+)$/mi', $article['content'], $rateMatch);
                $address = trim($addressMatch[1] ?? '');
                $rate = trim($rateMatch[1] ?? '');
                $location = $address !== '' && mb_strtolower($address) !== 'null'
                    ? $address
                    : 'address not listed';
                $line = '- **'.$name.'** — '.$location;

                if ($rate !== '' && mb_strtolower($rate) !== 'not listed') {
                    $hasRate = true;
                    $line .= '; rate: '.$rate;
                }

                return $line;
            });
            $intro = $lang === 'fil'
                ? 'Narito ang accommodation listings na nakita sa verified tourism data:'
                : 'These accommodations appear in the verified tourism data:';
            $rateNote = $hasRate
                ? ($lang === 'fil'
                    ? 'Ang mga rate ay mula sa live MinTour database; kumpirmahin bago mag-book.'
                    : 'Rates above come from the live MinTour database; confirm before booking.')
                : ($lang === 'fil'
                    ? 'Walang nakalistang nightly rates sa mga entry na ito; hindi ako magtatantiya ng presyo.'
                    : 'Nightly rates are not listed in these entries, so I can’t estimate a price.');

            return $intro."\n".$lines->implode("\n")."\n\n".$rateNote;
        }

        if ($isRecommendation && preg_match('/\b(eat|eating|dining|food|restaurant|restaurants|kumain|kainan|pagkain)\b/iu', $lookup)) {
            $restaurants = collect($articles)
                ->filter(fn (array $article) => preg_match('/restaurant|cafe/i', ($article['category'] ?? '').' '.($article['title'] ?? '')) === 1)
                ->unique(fn (array $article) => mb_strtolower($this->cleanListingTitle($article['title'])))
                ->take(5);

            if ($restaurants->isEmpty()) {
                return null;
            }

            $lines = $restaurants->map(function (array $article) {
                $name = $this->cleanListingTitle($article['title']);
                preg_match('/^Address:\s*(.+)$/mi', $article['content'], $addressMatch);
                preg_match('/^Price range:\s*(.+)$/mi', $article['content'], $priceMatch);
                $address = trim($addressMatch[1] ?? '');
                $price = trim($priceMatch[1] ?? '');
                $line = '- **'.$name.'** — '.($address !== '' && mb_strtolower($address) !== 'null' ? $address : 'address not listed');

                if ($price !== '' && mb_strtolower($price) !== 'not listed') {
                    $line .= '; price range: '.$price;
                }

                return $line;
            });
            $intro = $lang === 'fil'
                ? 'Narito ang mga kainan na nasa verified tourism listings:'
                : 'These food places appear in the verified tourism listings:';
            $priceNote = $lang === 'fil'
                ? 'Hindi nakalista sa mga entry na ito ang menu o presyo.'
                : 'Menus and prices are not listed in these entries.';

            return $intro."\n".$lines->implode("\n")."\n\n".$priceNote;
        }

        $tourismOffice = collect($portalArticles)->first(
            fn (array $article) => str_contains(mb_strtolower($article['category'] ?? ''), 'tourism office'),
        );
        if ($tourismOffice && preg_match('/tourism office|opisina ng turismo/iu', $lookup)) {
            $content = preg_replace('/\n\nSource:.*$/s', '', $tourismOffice['content']);
            $intro = $lang === 'fil'
                ? 'Ayon sa verified tourism directory:'
                : 'According to the verified tourism directory:';

            return $intro."\n\n".$content;
        }

        $festival = collect($portalArticles)->first(
            fn (array $article) => str_contains(mb_strtolower($article['category'] ?? ''), 'event')
                && str_contains(mb_strtolower($article['title']), 'festival'),
        );
        if ($festival && preg_match('/festival|pista|upcoming|darating/iu', $lookup)) {
            $content = preg_replace('/\n\nSource:.*$/s', '', $festival['content']);
            if (preg_match('/\b(fees?|price|cost|presyo|magkano|bayad|gastos)\b/iu', $question)) {
                $content .= $lang === 'fil'
                    ? "\n\nWalang nakalistang event o entrance fee; kumpirmahin ang kasalukuyang detalye sa lokal na pamahalaan."
                    : "\n\nNo event or entrance fee is listed; confirm current details with the local government.";
            }
            $intro = $lang === 'fil'
                ? 'Ayon sa verified festival listing:'
                : 'According to the verified festival listing:';

            return $intro."\n\n".$content;
        }

        $place = collect($portalArticles)->first(function (array $article) use ($lookup) {
            if (! in_array($article['category'], ['Attraction', 'Nature', 'Adventure'], true)) {
                return false;
            }

            $name = $this->cleanListingTitle($article['title']);
            $nameTerms = preg_split('/[\s\p{P}]+/u', mb_strtolower($name), -1, PREG_SPLIT_NO_EMPTY);
            $matches = collect($nameTerms)
                ->filter(fn (string $term) => mb_strlen($term) >= 4
                    && preg_match('/(?<![\pL\pN])'.preg_quote($term, '/').'(?![\pL\pN])/u', mb_strtolower($lookup)) === 1)
                ->count();

            return $matches >= 2;
        });

        if (! $place) {
            return null;
        }

        $name = $this->cleanListingTitle($place['title']);

        $asksEntranceFee = preg_match('/\b(entrance|admission|fees?|price|cost|presyo|magkano|bayad|bayarin|gastos|libre|free)\b/iu', $question);
        if ($asksEntranceFee) {
            preg_match('/^Entrance fee:\s*(.+)$/mi', $place['content'], $feeMatch);

            if (! empty($feeMatch[1])) {
                $fee = trim($feeMatch[1]);
                $intro = $lang === 'fil'
                    ? 'Ayon sa verified tourism listing, ang nakalistang entrance fee para sa'
                    : 'According to the verified tourism listing, the listed entrance fee for';

                return "{$intro} **{$name}** ay {$fee}.";
            }

            return $lang === 'fil'
                ? "Walang nakalistang entrance fee o ibang presyo para sa **{$name}** sa tourism listing; hindi ito nangangahulugang libre ang pagbisita."
                : "The tourism listing does not state an entrance fee or other price for **{$name}**; that does not mean the visit is free.";
        }

        $asksSchedule = preg_match('/\b(hours?|open|opening|close|closes|closing|schedule|bukas|sarado|oras)\b/iu', $question);
        if ($asksSchedule) {
            preg_match('/^[^:\n]*\b(?:hours?|open|opening|closes?|closing|schedule)\b[^:\n]*:\s*(.+)$/mi', $place['content'], $hoursMatch);

            if (! empty($hoursMatch[1]) && mb_strtolower(trim($hoursMatch[1])) !== 'null') {
                $hours = trim($hoursMatch[1]);

                return $lang === 'fil'
                    ? "Ayon sa verified tourism listing, ang nakalistang oras para sa **{$name}**: {$hours}."
                    : "According to the verified tourism listing, the listed hours for **{$name}**: {$hours}.";
            }

            return $lang === 'fil'
                ? "Walang nakalistang opening hours para sa **{$name}** sa tourism listing; kumpirmahin ang kasalukuyang schedule sa lokal na tourism office."
                : "The tourism listing does not state opening hours for **{$name}**; confirm the current schedule with the local tourism office.";
        }

        $asksContact = preg_match('/\b(contact|phone|number|email|call|tawag|numero|telepono)\b/iu', $question);
        if ($asksContact) {
            preg_match('/^Phone:\s*(.+)$/mi', $place['content'], $phoneMatch);
            preg_match('/^Email:\s*(.+)$/mi', $place['content'], $emailMatch);
            $details = array_filter([
                ! empty($phoneMatch[1]) && mb_strtolower(trim($phoneMatch[1])) !== 'null' ? 'Phone: '.trim($phoneMatch[1]) : null,
                ! empty($emailMatch[1]) && mb_strtolower(trim($emailMatch[1])) !== 'null' ? 'Email: '.trim($emailMatch[1]) : null,
            ]);

            if ($details !== []) {
                return $lang === 'fil'
                    ? "Ayon sa verified tourism listing, ang contact details para sa **{$name}**:\n- ".implode("\n- ", $details)
                    : "According to the verified tourism listing, the contact details for **{$name}**:\n- ".implode("\n- ", $details);
            }

            return $lang === 'fil'
                ? "Walang nakalistang contact number o email para sa **{$name}**; makipag-ugnayan sa Municipal Tourism Office para sa pinakabagong detalye."
                : "The tourism listing does not provide a contact number or email for **{$name}**; check with the Municipal Tourism Office for current details.";
        }

        $asksAddress = preg_match('/\b(address|location|where|saan|directions|papunta)\b/iu', $question);
        if ($asksAddress) {
            preg_match('/^Address:\s*(.+)$/mi', $place['content'], $addressMatch);

            if (! empty($addressMatch[1]) && mb_strtolower(trim($addressMatch[1])) !== 'null') {
                $address = trim($addressMatch[1]);

                return $lang === 'fil'
                    ? "Ayon sa verified tourism listing, ang address ng **{$name}** ay {$address}."
                    : "According to the verified tourism listing, **{$name}** is at {$address}.";
            }

            return $lang === 'fil'
                ? "Walang nakalistang address para sa **{$name}** sa tourism listing; kumpirmahin ang lokasyon sa Municipal Tourism Office."
                : "The tourism listing does not state an address for **{$name}**; confirm the location with the Municipal Tourism Office.";
        }

        $summary = trim(Str::before($place['content'], "\n\n"));
        if ($summary === '' || mb_strtolower($summary) === 'null') {
            return null;
        }

        $intro = $lang === 'fil'
            ? 'Ayon sa verified tourism listing'
            : 'According to the verified tourism listing';

        return "{$intro}, **{$name}**: {$summary}";
    }

    private function isRecommendationRequest(string $question): bool
    {
        return preg_match(
            '/\b(?:best|top|recommend(?:ation)?s?|suggest(?:ion)?s?|where\s+to|where\s+can\s+(?:i|we)|places?\s+to|options?|list)\b'
            .'|saan\s+(?:(?:ako|kami|tayo)\s+)?(?:puwede|pwede|maaaring)?\s*(?:kumain|pumunta|mag-stay|mamasyal|magbakasyon)'
            .'|(?:ano|anong)\s+(?:ang\s+)?(?:mga\s+)?(?:puwedeng|pwedeng|maaaring)\s+(?:kainan|puntahan|pasyalan|tuluyan)'
            .'|(?:irekomenda|magrekomenda|rekomendasyon|pinakamagandang|pinakamahusay|mga\s+(?:beach|restaurant|restaurants|hotel|hotels|kainan))\b/iu',
            $question,
        ) === 1;
    }

    private function cleanListingTitle(string $title): string
    {
        return (string) preg_replace(
            '/\s+—\s+(?:official tourism listing|festival information|live (?:destination|accommodation|restaurant|event|product|faq|emergency contact) record)$/u',
            '',
            $title,
        );
    }

    /** Resolve the current session from the authenticated user or guest token. */
    private function resolveSession(): ChatbotSession
    {
        $user = auth()->user();

        if ($user) {
            return ChatbotSession::firstOrCreate(
                ['user_id' => $user->id, 'guest_token' => null],
                [
                    'lang' => $user->preferred_lang ?? 'en',
                    'ip_address' => request()->ip(),
                    'user_agent' => Str::limit(request()->userAgent() ?? '', 500),
                    'last_active_at' => now(),
                ]
            );
        }

        $token = request()->cookie('mintour_guest');
        if (! $token || mb_strlen($token) < 20) {
            $token = Str::random(48);
            cookie()->queue(cookie('mintour_guest', $token, 60 * 24 * 30, '/', null, false, true, false, 'lax'));
        }

        return ChatbotSession::firstOrCreate(
            ['guest_token' => $token, 'user_id' => null],
            [
                'lang' => 'en',
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent() ?? '', 500),
                'last_active_at' => now(),
            ]
        );
    }

    private function logUnanswered(ChatbotSession $session, string $question, string $lang): void
    {
        // Don't log duplicates within a 30-minute window.
        $recent = UnansweredQuestion::query()
            ->where('question', $question)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($recent) {
            return;
        }

        UnansweredQuestion::create([
            'chatbot_session_id' => $session->id,
            'user_id' => $session->user_id,
            'question' => $question,
            'lang' => $lang,
        ]);
    }

    private function fallback(string $lang): string
    {
        return $lang === 'fil'
            ? "Wala pa akong beripikadong impormasyon tungkol diyan — pero naitala ko na ang tanong mo para sa tourism team. Maaari mo ring subukan ang Municipal Tourism Office para sa pinakabagong detalye. Puwede rin akong magbigay ng pangkalahatang kaalaman tungkol sa paglalakbay, kultura, o kasaysayan ng Pilipinas. 🌴"
            : "I don't have verified info on that yet — but I've logged your question for our tourism team. You can also check with the Municipal Tourism Office for the latest details. I can still help with general knowledge about travel, Philippine culture, or history. 🌴";
    }

    private function looksLikeFallback(string $reply): bool
    {
        $needle = [
            "I don't have verified info",
            'Wala pa akong beripikadong impormasyon',
            'Wala akong beripikadong impormasyon',
        ];

        foreach ($needle as $n) {
            if (Str::contains($reply, $n)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Minimal markdown → HTML. Groq is instructed to bold key terms with
     * **double asterisks** and to avoid arbitrary HTML, so we keep this tight.
     * All output is escaped first, so no XSS vector.
     */
    private function markdownToHtml(string $text): string
    {
        $escaped = e($text);

        // **bold**
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);

        // preserve paragraph breaks
        $escaped = nl2br($escaped);

        return $escaped;
    }
}
