<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\EmergencyContact;
use App\Models\Event;
use App\Models\KnowledgeArticle;
use App\Models\Restaurant;
use App\Services\KnowledgeRetriever;
use App\Services\LanguageDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGroq(string $reply = 'The best beach is **Puting Buhangin Cove**.', int $tokens = 42): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['role' => 'assistant', 'content' => $reply],
                    'finish_reason' => 'stop',
                ]],
                'usage' => ['total_tokens' => $tokens],
            ], 200),
        ]);
    }

    public function test_guest_can_send_a_message_and_receives_a_reply(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq();

        $response = $this->postJson('/api/chat/message', ['message' => 'Best beach?', 'lang' => 'en']);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('lang', 'en')
            ->assertJsonStructure(['reply', 'html', 'message_id', 'session_id']);

        $this->assertDatabaseCount('chatbot_sessions', 1);
        $this->assertDatabaseCount('chatbot_messages', 2);

        $request = Http::recorded()->first()[0];
        $this->assertSame('https://api.groq.com/openai/v1/chat/completions', $request->url());
        $this->assertSame('openai/gpt-oss-20b', $request->data()['model']);
        $this->assertSame(['Bearer test-key'], $request->header('Authorization'));
    }

    public function test_retriever_surfaces_relevant_article(): void
    {
        KnowledgeArticle::create([
            'title' => 'Best beaches',
            'category' => 'Beaches',
            'content' => 'Puting Buhangin Cove is the top beach 24 km from town.',
            'source' => 'https://www.travelorientalmindoro.ph/place/puting-buhangin-cove',
            'is_published' => true,
        ]);

        $hits = app(KnowledgeRetriever::class)->retrieve('best beach near Victoria', verifiedOnly: true);

        $this->assertNotEmpty($hits);
        $this->assertEquals('Best beaches', $hits[0]['title']);
    }

    public function test_retriever_matches_tagalog_interests_and_omits_unverified_articles(): void
    {
        KnowledgeArticle::create([
            'title' => 'Mabalbay Falls — official tourism listing',
            'category' => 'Attraction',
            'content' => 'Mabalbay Falls is a nature attraction with a trekking trail.',
            'source' => 'https://www.travelorientalmindoro.ph/place/mabalbay-falls',
            'tags' => ['Victoria', 'waterfall', 'tourism'],
            'is_published' => true,
        ]);
        KnowledgeArticle::create([
            'title' => 'Unverified Falls',
            'category' => 'Attraction',
            'content' => 'A waterfall listed without a source.',
            'is_published' => true,
        ]);

        $hits = app(KnowledgeRetriever::class)->retrieve('May talon ba?', verifiedOnly: true);

        $this->assertCount(1, $hits);
        $this->assertSame('Mabalbay Falls — official tourism listing', $hits[0]['title']);
    }

    public function test_recommended_questions_retrieve_relevant_verified_articles_in_english_and_filipino(): void
    {
        $articles = [
            ['Mabalbay Falls — official tourism listing', 'Attraction', 'Mabalbay Falls is a waterfall in Victoria.', 'https://www.travelorientalmindoro.ph/place/mabalbay-falls'],
            ['Bradz Restaurant — official tourism listing', 'Restaurant', 'Bradz Restaurant is listed in Victoria.', 'https://www.travelorientalmindoro.ph/place/bradz-restaurant'],
            ['Bato Creek Kayaking — official tourism listing', 'Attraction', 'Bato Creek in Victoria offers kayaking and whitewater rafting.', 'https://www.travelorientalmindoro.ph/place/bato-creek'],
            ['Kapakyanan Festival — festival information', 'Events', 'Kapakyanan Festival is an annual festival in Victoria.', 'https://www.travelorientalmindoro.ph/festival/kapakyanan-festival'],
            ['Victoria Municipal Tourism Office — contact information', 'Tourism Office', 'Victoria Municipal Tourism Office contact information.', 'https://www.travelorientalmindoro.ph/directory/tourism-offices'],
            ['Victoria Nature Park — official tourism listing', 'Attraction', 'The park features agricultural demonstrations.', 'https://www.travelorientalmindoro.ph/place/victoria-nature-park'],
        ];

        foreach ($articles as [$title, $category, $content, $source]) {
            KnowledgeArticle::create([
                'title' => $title,
                'category' => $category,
                'content' => $content,
                'source' => $source,
                'is_published' => true,
            ]);
        }

        $retriever = app(KnowledgeRetriever::class);
        $questions = [
            'Tell me about Mabalbay Falls' => 'Mabalbay Falls — official tourism listing',
            'Ano ang Mabalbay Falls?' => 'Mabalbay Falls — official tourism listing',
            'Where to eat in Victoria?' => 'Bradz Restaurant — official tourism listing',
            'Saan puwedeng kumain sa Victoria?' => 'Bradz Restaurant — official tourism listing',
            'What can I do at Bato Creek?' => 'Bato Creek Kayaking — official tourism listing',
            'Ano ang puwedeng gawin sa Bato Creek?' => 'Bato Creek Kayaking — official tourism listing',
            'When is Kapakyanan Festival?' => 'Kapakyanan Festival — festival information',
            'Kailan ang Kapakyanan Festival?' => 'Kapakyanan Festival — festival information',
            'Where is the Tourism Office?' => 'Victoria Municipal Tourism Office — contact information',
            'Saan ang Tourism Office ng Victoria?' => 'Victoria Municipal Tourism Office — contact information',
        ];

        foreach ($questions as $question => $expectedTitle) {
            $hits = $retriever->retrieve($question, 5, verifiedOnly: true);
            $this->assertNotEmpty($hits, "No verified article found for: {$question}");
            $this->assertSame($expectedTitle, $hits[0]['title'], "Wrong top article for: {$question}");
        }

        $restaurantHits = $retriever->retrieve('Where to eat in Victoria?', 5, verifiedOnly: true);
        $this->assertSame(
            ['Restaurant'],
            array_values(array_unique(array_column($restaurantHits, 'category'))),
            'Food recommendations must not include articles matched only by a substring such as "eat" in "features".',
        );
    }

    public function test_recommended_question_answers_use_only_verified_directory_facts(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('Invented restaurant cuisines and prices.');

        foreach ([
            [
                'title' => 'Bradz Restaurant — official tourism listing',
                'category' => 'Restaurant',
                'content' => "Bradz Restaurant is a local restaurant.\n\nAddress: San Isidro, Victoria, Oriental Mindoro\n\nPhone: 0920-374-7154",
                'source' => 'https://www.travelorientalmindoro.ph/place/bradz-restaurant',
            ],
            [
                'title' => 'Atienza’s Beach Resort — official tourism listing',
                'category' => 'Resort',
                'content' => "Atienza’s Beach Resort is listed by the tourism portal.\n\nAddress: NULL\n\nEmail: contact@example.com",
                'source' => 'https://www.travelorientalmindoro.ph/place/atienzas-beach-resort',
            ],
            [
                'title' => 'Victoria’s Bed and Breakfast — official tourism listing',
                'category' => 'Hotel',
                'content' => "Victoria’s Bed and Breakfast is in San Isidro (White Beach), Puerto Galera.\n\nAddress: San Isidro (White Beach), Puerto Galera",
                'source' => 'https://www.travelorientalmindoro.ph/place/victorias-bed-and-breakfast',
            ],
            [
                'title' => '88 Hotel — official tourism listing',
                'category' => 'Hotel',
                'content' => "88 Hotel provides accommodations.\n\nAddress: Poblacion III, Victoria, Oriental Mindoro",
                'source' => 'https://www.travelorientalmindoro.ph/place/88-hotel',
            ],
            [
                'title' => 'Bato Creek Kayaking & White Water Rafting — official tourism listing',
                'category' => 'Attraction',
                'content' => "Bato Creek in Villa Cerveza, Victoria offers kayaking and whitewater rafting.\n\nAddress: Villa Cerveza, Victoria, Oriental Mindoro",
                'source' => 'https://www.travelorientalmindoro.ph/place/bato-creek',
            ],
            [
                'title' => 'Mabalbay Falls — official tourism listing',
                'category' => 'Attraction',
                'content' => "Mabalbay Falls is in Villa Cerveza, Victoria.\n\nAddress: Villa Cerveza, Victoria, Oriental Mindoro",
                'source' => 'https://www.travelorientalmindoro.ph/place/mabalbay-falls',
            ],
            [
                'title' => 'Kapakyanan Festival — festival information',
                'category' => 'Events',
                'content' => 'The portal lists the annual Kapakyanan Festival on September 18.',
                'source' => 'https://www.travelorientalmindoro.ph/festival/kapakyanan-festival',
            ],
            [
                'title' => 'Victoria Municipal Tourism Office — contact information',
                'category' => 'Tourism Office',
                'content' => "Municipal Tourism Office - Victoria\n\nAddress: Poblacion 1, Victoria\n\nPhone: 043-285-5348",
                'source' => 'https://www.travelorientalmindoro.ph/directory/tourism-offices',
            ],
        ] as $article) {
            KnowledgeArticle::create($article + ['is_published' => true]);
        }

        $this->postJson('/api/chat/message', ['message' => 'Where to eat in Victoria?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => "These food places appear in the verified tourism listings:\n- **Bradz Restaurant** — San Isidro, Victoria, Oriental Mindoro\n\nMenus and prices are not listed in these entries."])
            ->assertDontSee('Invented restaurant cuisines');

        $beachResponse = $this->postJson('/api/chat/message', ['message' => 'Best beach near Victoria?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true);
        $this->assertStringContainsString('Atienza’s Beach Resort', $beachResponse->json('reply'));
        $this->assertStringContainsString('address not listed', $beachResponse->json('reply'));
        $this->assertStringNotContainsString('Victoria’s Bed and Breakfast', $beachResponse->json('reply'));
        $this->assertStringNotContainsString('Atienza’s Beach Resort is the best', $beachResponse->json('reply'));

        $hotelResponse = $this->postJson('/api/chat/message', ['message' => 'Presyo ng hotel?', 'lang' => 'fil'])
            ->assertOk()
            ->assertJsonPath('grounded', true);
        $this->assertStringContainsString('88 Hotel', $hotelResponse->json('reply'));
        $this->assertStringContainsString('Walang nakalistang nightly rates', $hotelResponse->json('reply'));
        $this->assertStringNotContainsString('₱', $hotelResponse->json('reply'));

        $this->postJson('/api/chat/message', ['message' => 'Tell me about Mabalbay Falls', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => 'According to the verified tourism listing, **Mabalbay Falls**: Mabalbay Falls is in Villa Cerveza, Victoria.']);

        $this->postJson('/api/chat/message', ['message' => 'Magkano ang entrance fee sa Mabalbay Falls?', 'lang' => 'fil'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => 'Walang nakalistang entrance fee o ibang presyo para sa **Mabalbay Falls** sa tourism listing; hindi ito nangangahulugang libre ang pagbisita.']);

        $this->postJson('/api/chat/message', ['message' => 'What can I do at Bato Creek?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => 'According to the verified tourism listing, **Bato Creek Kayaking & White Water Rafting**: Bato Creek in Villa Cerveza, Victoria offers kayaking and whitewater rafting.']);

        $this->postJson('/api/chat/message', ['message' => 'When is Kapakyanan Festival?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => "According to the verified festival listing:\n\nThe portal lists the annual Kapakyanan Festival on September 18."]);

        $festivalFeeResponse = $this->postJson('/api/chat/message', ['message' => 'How much does the Kapakyanan Festival cost?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', true);
        $this->assertStringContainsString('No event or entrance fee is listed', $festivalFeeResponse->json('reply'));

        $this->postJson('/api/chat/message', ['message' => 'Saan ang Tourism Office ng Victoria?', 'lang' => 'fil'])
            ->assertOk()
            ->assertJsonPath('grounded', true)
            ->assertJsonFragment(['reply' => "Ayon sa verified tourism directory:\n\nMunicipal Tourism Office - Victoria\n\nAddress: Poblacion 1, Victoria\n\nPhone: 043-285-5348"]);

        Http::assertNothingSent();
    }

    public function test_generic_best_destination_question_retrieves_published_destinations(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('A good place to start is **Victoria Municipal Hall & Plaza**, a convenient first stop in town.');
        Destination::factory()->create([
            'name' => 'Victoria Municipal Hall & Plaza',
            'slug' => 'victoria-municipal-hall-plaza',
            'short_description' => 'A convenient first stop in Victoria town center.',
            'long_description' => 'Visit the public plaza in the town center.',
            'is_featured' => true,
        ]);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'what is the best destination',
            'lang' => 'en',
        ])->assertOk()
            ->assertJsonPath('grounded', true);

        $this->assertStringContainsString('Victoria Municipal Hall & Plaza', $response->json('reply'));
        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])->pluck('content')->implode("\n");
        $this->assertStringContainsString('Victoria Municipal Hall & Plaza', $prompt);
        $this->assertStringContainsString('live destination record', $prompt);
    }

    public function test_beach_detail_question_is_answered_for_its_intent_instead_of_returning_a_generic_list(): void
    {
        config()->set('groq.key', 'test-key');
        KnowledgeArticle::create([
            'title' => 'Puting Buhangin Beach — official tourism listing',
            'category' => 'Attraction',
            'content' => "Puting Buhangin Beach is in Victoria.\n\nOpening hours: 8:00 AM - 5:00 PM",
            'source' => 'https://www.travelorientalmindoro.ph/place/puting-buhangin-beach',
            'is_published' => true,
        ]);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'What time does Puting Buhangin Beach open?',
            'lang' => 'en',
        ])->assertOk();

        $this->assertSame(
            'According to the verified tourism listing, the listed hours for **Puting Buhangin Beach**: 8:00 AM - 5:00 PM.',
            $response->json('reply'),
        );
        Http::assertNothingSent();
    }

    public function test_restaurant_detail_question_is_not_replaced_by_a_generic_recommendation_list(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('The listing shows the restaurant is open until 8 PM.');
        KnowledgeArticle::create([
            'title' => 'Bradz Restaurant — official tourism listing',
            'category' => 'Restaurant',
            'content' => "Bradz Restaurant serves local food.\n\nOpening hours: 8:00 AM - 8:00 PM",
            'source' => 'https://www.travelorientalmindoro.ph/place/bradz-restaurant',
            'is_published' => true,
        ]);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'What time does Bradz Restaurant close?',
            'lang' => 'en',
        ])->assertOk();

        $this->assertSame('The listing shows the restaurant is open until 8 PM.', $response->json('reply'));
        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])->pluck('content')->implode("\n");
        $this->assertStringContainsString('Opening hours: 8:00 AM - 8:00 PM', $prompt);
    }

    public function test_chat_prompt_receives_portal_and_curated_local_articles_but_not_arbitrary_articles(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq();
        KnowledgeArticle::create([
            'title' => 'Verified Victoria municipality profile',
            'category' => 'Municipality',
            'content' => 'Victoria is a municipality in Oriental Mindoro with a tourism office.',
            'source' => 'https://www.travelorientalmindoro.ph/municipality/victoria',
            'is_published' => true,
        ]);
        KnowledgeArticle::create([
            'title' => 'Unverified secret beach',
            'category' => 'Beaches',
            'content' => 'An unsourced place.',
            'is_published' => true,
        ]);
        $this->assertCount(
            1,
            app(KnowledgeRetriever::class)->retrieve('Tell me about Victoria municipality', verifiedOnly: true),
        );

        $this->postJson('/api/chat/message', ['message' => 'Tell me about Victoria municipality'])
            ->assertOk();

        $request = Http::recorded()->first()[0];
        $prompt = collect($request->data()['messages'])
            ->pluck('content')
            ->implode("\n");
        $this->assertStringContainsString('Verified Victoria municipality profile', $prompt, $prompt);
        $this->assertStringNotContainsString('Unverified secret beach', $prompt, $prompt);
    }

    public function test_chat_prompt_includes_relevant_curated_local_directory_articles(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq();
        KnowledgeArticle::create([
            'title' => 'How to reach Victoria from Manila',
            'category' => 'Transport',
            'content' => 'Bus, ferry, then van to Victoria. Confirm schedules and fares before travel.',
            'source' => 'MinTour directory; confirm current details locally',
            'is_published' => true,
        ]);

        $this->postJson('/api/chat/message', ['message' => 'How do I get from Manila to Victoria?'])
            ->assertOk();

        $request = Http::recorded()->first()[0];
        $prompt = collect($request->data()['messages'])
            ->pluck('content')
            ->implode("\n");
        $this->assertStringContainsString('How to reach Victoria from Manila', $prompt, $prompt);
        $this->assertStringContainsString('MinTour directory/map record (not official; confirm current details)', $prompt, $prompt);
    }

    public function test_curated_directory_articles_are_retrievable_but_arbitrary_sources_remain_excluded(): void
    {
        KnowledgeArticle::create([
            'title' => 'How to reach Victoria from Manila',
            'category' => 'Transport',
            'content' => 'Bus to Batangas Pier, ferry to Calapan, then van to Victoria.',
            'source' => 'MinTour directory; confirm current details locally',
            'is_published' => true,
        ]);
        KnowledgeArticle::create([
            'title' => 'Unverified Manila transport route',
            'category' => 'Transport',
            'content' => 'An unsourced route to Victoria.',
            'source' => 'untrusted.example',
            'is_published' => true,
        ]);

        $hits = app(KnowledgeRetriever::class)->retrieve(
            'Paano pumunta sa Victoria?',
            verifiedOnly: true,
            includeLocalDirectory: true,
        );

        $this->assertSame(['How to reach Victoria from Manila'], array_column($hits, 'title'));
    }

    public function test_missing_kb_logs_unanswered_question_and_returns_fallback(): void
    {
        config()->set('groq.key', 'test-key');
        // Groq returns its own fallback phrase.
        $this->fakeGroq("I don't have verified info on that yet — but I've logged your question for our tourism team.");

        $this->postJson('/api/chat/message', ['message' => 'Do you have an ATM in Silonay?', 'lang' => 'en'])
            ->assertOk()
            ->assertJsonPath('grounded', false);

        $this->assertDatabaseHas('unanswered_questions', [
            'question' => 'Do you have an ATM in Silonay?',
            'lang' => 'en',
        ]);
    }

    public function test_fallback_turns_are_not_replayed_as_groq_conversation_history(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq("I don't have verified info on that yet — but I've logged your question for our tourism team.");
        KnowledgeArticle::create([
            'title' => 'Victoria, Oriental Mindoro — municipality profile',
            'category' => 'Municipality',
            'content' => 'Victoria has a tourism office and agricultural destinations.',
            'source' => 'https://www.travelorientalmindoro.ph/municipality/victoria',
            'is_published' => true,
        ]);

        $this->postJson('/api/chat/message', ['message' => 'Old unanswered question'])
            ->assertOk();
        $this->postJson('/api/chat/message', ['message' => 'Tell me about Victoria municipality'])
            ->assertOk();

        $requests = Http::recorded()->values();
        $messages = $requests[1][0]->data()['messages'];
        $this->assertCount(2, $messages);
        $this->assertStringNotContainsString('Old unanswered question', json_encode($messages));
        $this->assertStringContainsString('Victoria has a tourism office', $messages[1]['content']);
    }

    public function test_guest_token_is_reused_across_requests(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq();

        $firstResponse = $this->postJson('/api/chat/message', ['message' => 'Hello']);
        $guestCookie = $firstResponse->getCookie('mintour_guest', false);
        $this->assertNotNull($guestCookie);

        $this->withCredentials()
            ->withUnencryptedCookie('mintour_guest', $guestCookie->getValue())
            ->postJson('/api/chat/message', ['message' => 'Another question']);

        $this->assertDatabaseCount('chatbot_sessions', 1);
        $this->assertDatabaseCount('chatbot_messages', 4);
    }

    public function test_feedback_is_recorded_on_message(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq();

        $res = $this->postJson('/api/chat/message', ['message' => 'Hi']);
        $id = $res->json('message_id');

        $this->postJson("/api/chat/feedback/{$id}", ['feedback' => 'helpful'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('chatbot_messages', [
            'id' => $id,
            'feedback' => 'helpful',
        ]);
    }

    public function test_rate_limit_blocks_after_20_requests(): void
    {
        config()->set('groq.key', 'test-key');
        config()->set('groq.rate_limit', 3);
        $this->fakeGroq();

        // Trigger limiter with the correct key.
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/chat/message', ['message' => "msg {$i}"])->assertOk();
        }

        // 4th call within the same minute should be throttled.
        $this->postJson('/api/chat/message', ['message' => 'throttled'])
            ->assertStatus(429);
    }

    public function test_when_groq_fails_service_returns_an_explicit_error_not_a_missing_information_fallback(): void
    {
        config()->set('groq.key', 'test-key');

        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => ['code' => 500, 'message' => 'Internal'],
            ], 500),
        ]);

        $res = $this->postJson('/api/chat/message', ['message' => 'Best beach?']);

        $res->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('grounded', false)
            ->assertJsonPath('error', 'Groq AI is temporarily unavailable. Please try again later.')
            ->assertJsonPath('reply', 'Groq AI is temporarily unavailable. Please try again later.');
        $this->assertDatabaseMissing('unanswered_questions', ['question' => 'Best beach?']);
        $this->assertDatabaseHas('chatbot_messages', [
            'role' => 'assistant',
            'content' => 'Groq AI is temporarily unavailable. Please try again later.',
        ]);
    }

    public function test_missing_groq_key_is_reported_without_logging_a_tourism_gap(): void
    {
        config()->set('groq.key', null);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'Tell me about Victoria',
            'lang' => 'fil',
        ]);

        $response->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('grounded', false)
            ->assertJsonPath('error', 'Hindi pa naka-configure ang Groq AI. Pakisuri ang GROQ_API_KEY sa server .env.')
            ->assertJsonPath('reply', 'Hindi pa naka-configure ang Groq AI. Pakisuri ang GROQ_API_KEY sa server .env.');
        $this->assertDatabaseMissing('unanswered_questions', ['question' => 'Tell me about Victoria']);
    }

    public function test_unavailable_groq_model_has_a_localized_actionable_error(): void
    {
        config()->set('groq.key', 'test-key');
        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => ['message' => 'Model not found'],
            ], 404),
        ]);

        $this->postJson('/api/chat/message', [
            'message' => 'Saan puwedeng kumain sa Victoria?',
            'lang' => 'fil',
        ])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('error', 'Hindi available ang Groq model na naka-configure. Pakisuri ang GROQ_MODEL sa server settings.');
    }

    public function test_general_questions_use_general_knowledge_and_are_not_logged_as_unanswered(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('General knowledge: Tokyo is the capital of Japan.');

        $response = $this->postJson('/api/chat/message', [
            'message' => 'What is the capital of Japan?',
            'lang' => 'en',
        ]);

        $response->assertOk()
            ->assertJsonPath('grounded', false);

        $this->assertStringContainsString('Tokyo', $response->json('reply'));
        $this->assertDatabaseMissing('unanswered_questions', [
            'question' => 'What is the capital of Japan?',
        ]);

        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])
            ->pluck('content')
            ->implode("\n");
        $this->assertStringContainsString('GENERAL KNOWLEDGE', $prompt);
        $this->assertStringContainsString('CURRENT DATE', $prompt);
        $this->assertStringContainsString('No matching verified local passages', $prompt);
        $this->assertStringContainsString('What is the capital of Japan?', $prompt);
    }

    public function test_mangyan_geography_question_uses_general_knowledge_without_claiming_local_listing(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('Sa pangkalahatang kaalaman: Ang Mangyan ay mga katutubong pangkat ng Mindoro. Hindi ito beripikadong listing ng isang partikular na komunidad sa Victoria.');

        $response = $this->postJson('/api/chat/message', [
            'message' => 'saan matatagpuan ang mangyan?',
            'lang' => 'fil',
        ])->assertOk()
            ->assertJsonPath('grounded', false);

        $this->assertStringContainsString('katutubong pangkat ng Mindoro', $response->json('reply'));
        $this->assertDatabaseMissing('unanswered_questions', [
            'question' => 'saan matatagpuan ang mangyan?',
        ]);

        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])->pluck('content')->implode("\n");
        $this->assertStringContainsString('Indigenous peoples and cultural groups', $prompt);
        $this->assertStringContainsString('do not claim a particular community or settlement in Victoria', $prompt);
    }

    public function test_new_short_question_does_not_inherit_the_previous_search_topic(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('General knowledge: The Mangyan are Indigenous peoples of Mindoro.');
        KnowledgeArticle::create([
            'title' => 'Mabalbay Falls — official tourism listing',
            'category' => 'Attraction',
            'content' => 'Mabalbay Falls is in Villa Cerveza, Victoria.',
            'source' => 'https://www.travelorientalmindoro.ph/place/mabalbay-falls',
            'is_published' => true,
        ]);

        $first = $this->postJson('/api/chat/message', [
            'message' => 'Tell me about Mabalbay Falls',
            'lang' => 'en',
        ])->assertOk();
        $guestCookie = $first->getCookie('mintour_guest', false);
        $this->assertNotNull($guestCookie);

        $this->withCredentials()
            ->withUnencryptedCookie('mintour_guest', $guestCookie->getValue())
            ->postJson('/api/chat/message', [
                'message' => 'saan matatagpuan ang mangyan?',
                'lang' => 'fil',
            ])->assertOk();

        $requests = Http::recorded()->values();
        $latestMessages = $requests[0][0]->data()['messages'];
        $this->assertStringNotContainsString('Mabalbay Falls is in Villa Cerveza', end($latestMessages)['content']);
        $this->assertStringContainsString('VISITOR QUESTION: saan matatagpuan ang mangyan?', end($latestMessages)['content']);
    }

    public function test_follow_up_questions_keep_the_previous_topic(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('unused');

        KnowledgeArticle::create([
            'title' => 'Mabalbay Falls — official tourism listing',
            'category' => 'Attraction',
            'content' => "Mabalbay Falls is in Villa Cerveza, Victoria.\n\nAddress: Villa Cerveza, Victoria, Oriental Mindoro",
            'source' => 'https://www.travelorientalmindoro.ph/place/mabalbay-falls',
            'is_published' => true,
        ]);

        $first = $this->postJson('/api/chat/message', [
            'message' => 'Tell me about Mabalbay Falls',
            'lang' => 'en',
        ])->assertOk();

        $guestCookie = $first->getCookie('mintour_guest', false);
        $this->assertNotNull($guestCookie);

        $second = $this->withCredentials()
            ->withUnencryptedCookie('mintour_guest', $guestCookie->getValue())
            ->postJson('/api/chat/message', [
                'message' => 'Magkano ang entrance fee?',
                'lang' => 'fil',
            ]);

        $second->assertOk()
            ->assertJsonPath('grounded', true);

        $this->assertStringContainsString('Mabalbay Falls', $second->json('reply'));
        $this->assertStringContainsString('Walang nakalistang entrance fee', $second->json('reply'));
        Http::assertNothingSent();
    }

    public function test_live_database_records_are_retrieved_and_reflect_updates(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('Here are the food options.');

        $restaurant = Restaurant::factory()->create([
            'name' => 'Old Harbour Diner',
            'cuisine' => 'Filipino',
            'address' => 'Poblacion, Victoria, Oriental Mindoro',
            'description' => 'Local meals and seafood.',
            'price_range' => 'PHP 100-250',
            'hours' => '8:00 AM - 8:00 PM',
        ]);

        $first = $this->postJson('/api/chat/message', [
            'message' => 'Where to eat in Victoria?',
            'lang' => 'en',
        ])->assertOk()
            ->assertJsonPath('grounded', true);
        $this->assertStringContainsString('Old Harbour Diner', $first->json('reply'));

        $restaurant->update(['name' => 'New Harbour Diner']);

        $second = $this->postJson('/api/chat/message', [
            'message' => 'Saan pwede kumain?',
            'lang' => 'fil',
        ])->assertOk()
            ->assertJsonPath('grounded', true);
        $this->assertStringContainsString('New Harbour Diner', $second->json('reply'));
        $this->assertStringNotContainsString('Old Harbour Diner', $second->json('reply'));
        Http::assertNothingSent();
    }

    public function test_live_accommodation_rates_are_used_instead_of_guessing(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('unused');

        Accommodation::factory()->create([
            'name' => 'Victoria Cove Resort',
            'type' => 'Resort',
            'address' => 'Poblacion, Victoria, Oriental Mindoro',
            'price_per_night' => 1500,
            'description' => 'A seaside resort in Victoria.',
        ]);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'Presyo ng hotel?',
            'lang' => 'fil',
        ]);

        $response->assertOk()
            ->assertJsonPath('grounded', true);

        $this->assertStringContainsString('Victoria Cove Resort', $response->json('reply'));
        $this->assertStringContainsString('PHP 1,500.00', $response->json('reply'));
        $this->assertStringContainsString('live MinTour database', $response->json('reply'));
        Http::assertNothingSent();
    }

    public function test_upcoming_events_are_retrieved_from_the_live_database(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('There is an upcoming festival in Victoria.');

        Event::factory()->create([
            'title' => 'Kapakyanan Festival',
            'location' => 'Victoria Plaza, Victoria, Oriental Mindoro',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addDay(),
            'description' => 'Annual cultural festival in Victoria.',
        ]);

        $this->postJson('/api/chat/message', [
            'message' => 'What events are upcoming in Victoria?',
            'lang' => 'en',
        ])->assertOk();

        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])
            ->pluck('content')
            ->implode("\n");

        $this->assertStringContainsString('Kapakyanan Festival', $prompt);
        $this->assertStringContainsString('live event record', $prompt);
        $this->assertStringContainsString('MinTour live database record (admin-maintained; may be more current)', $prompt);
    }

    public function test_emergency_contacts_are_retrieved_from_the_live_database(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('Call the emergency hotline for help.');

        EmergencyContact::factory()->create([
            'name' => 'Victoria Rescue Team',
            'phone' => '0917-555-0100',
            'category' => 'Emergency',
        ]);

        $this->postJson('/api/chat/message', [
            'message' => 'What is the emergency hotline?',
            'lang' => 'en',
        ])->assertOk();

        $prompt = collect(Http::recorded()->first()[0]->data()['messages'])
            ->pluck('content')
            ->implode("\n");
        $this->assertStringContainsString('Victoria Rescue Team', $prompt);
        $this->assertStringContainsString('0917-555-0100', $prompt);
        $this->assertStringContainsString('MinTour live database record (admin-maintained; may be more current)', $prompt);
    }

    public function test_empty_model_reply_returns_a_helpful_local_fallback_and_logs_a_gap(): void
    {
        config()->set('groq.key', 'test-key');
        $this->fakeGroq('');

        $response = $this->postJson('/api/chat/message', [
            'message' => 'Is there a secret cave in Victoria?',
            'lang' => 'en',
        ]);

        $response->assertOk()
            ->assertJsonPath('grounded', false);

        $this->assertStringContainsString('Municipal Tourism Office', $response->json('reply'));
        $this->assertDatabaseHas('unanswered_questions', [
            'question' => 'Is there a secret cave in Victoria?',
            'lang' => 'en',
        ]);
    }

    public function test_language_detector_flips_to_filipino_for_tagalog(): void
    {
        $detector = new LanguageDetector;

        $this->assertEquals('fil', $detector->detect('Saan masarap kumain dito?'));
        $this->assertEquals('fil', $detector->detect('May beach ba malapit?'));
        $this->assertEquals('fil', $detector->detect('Can you suggest a beach na sulit?'));
        $this->assertEquals('fil', $detector->detect('Ano?'));
        $this->assertEquals('fil', $detector->detect('Ilan ang tao doon?'));
        $this->assertEquals('en', $detector->detect('Where can I eat?'));
        $this->assertEquals('en', $detector->detect('Tell me about the beach'));
    }
}
