<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Retrieves the most relevant published KB articles for a user's query.
 *
 * Strategy:
 *  1. MySQL FULLTEXT match against title + content (fast, indexed).
 *  2. Fallback LIKE search when FULLTEXT returns nothing (short queries,
 *     stopwords, or when the FULLTEXT index hasn't been built yet).
 *  3. Boost by title matches, exact phrase matches, category hints, tags.
 *  4. Trim to top-N (config('groq.max_kb')).
 *
 * Articles are returned as a plain array so the caller can serialise them
 * straight into the AI prompt without leaking Eloquent internals.
 */
class KnowledgeRetriever
{
    /** Minimum relevance score an article needs to be considered a real hit. */
    private const MIN_SCORE = 3;

    public function retrieve(
        string $query,
        ?int $limit = null,
        bool $verifiedOnly = false,
        bool $includeLocalDirectory = false,
    ): array
    {
        $limit = $limit ?? config('groq.max_kb');
        $query = trim($query);

        if ($query === '' || mb_strlen($query) < 3) {
            return [];
        }

        $candidates = $this->fulltextSearch($query, $verifiedOnly, $includeLocalDirectory);

        if ($candidates->isEmpty()) {
            $candidates = $this->likeFallback($query, $verifiedOnly, $includeLocalDirectory);
        }

        $candidates = $candidates->unique('id')->values();

        if ($candidates->isEmpty()) {
            return [];
        }

        $terms = $this->expandTerms($this->extractTerms($query));

        return $candidates
            ->map(fn (KnowledgeArticle $a) => [
                'article' => $a,
                'score' => $this->score($a, $query, $terms),
            ])
            ->filter(fn ($row) => $row['score'] >= self::MIN_SCORE)
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(fn ($row) => $this->format($row['article']))
            ->all();
    }

    private function fulltextSearch(string $query, bool $verifiedOnly, bool $includeLocalDirectory): Collection
    {
        $terms = $this->expandTerms($this->extractTerms($query));

        if (empty($terms)) {
            return collect();
        }

        // Boolean FULLTEXT treats unrequired terms as alternatives; scoring below
        // ranks those candidates without demanding every synonym in the query.
        $boolean = collect($terms)
            ->map(fn ($t) => preg_replace('/[+\-><\(\)~*"@]+/', '', $t).'*')
            ->implode(' ');

        try {
            return $this->publishedArticles($verifiedOnly, $includeLocalDirectory)
                ->whereRaw('MATCH(title, content) AGAINST (? IN BOOLEAN MODE)', [$boolean])
                ->get();
        } catch (\Throwable $e) {
            // FULLTEXT index missing on this install — fall through to LIKE.
            report($e);

            return collect();
        }
    }

    private function likeFallback(string $query, bool $verifiedOnly, bool $includeLocalDirectory): Collection
    {
        $terms = $this->expandTerms($this->extractTerms($query));
        if (empty($terms)) {
            return collect();
        }

        return $this->publishedArticles($verifiedOnly, $includeLocalDirectory)
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('title', 'like', "%{$t}%")
                        ->orWhere('content', 'like', "%{$t}%")
                        ->orWhere('category', 'like', "%{$t}%")
                        ->orWhere('tags', 'like', "%{$t}%");
                }
            })
            ->get();
    }

    private function publishedArticles(bool $verifiedOnly, bool $includeLocalDirectory): Builder
    {
        return KnowledgeArticle::query()
            ->published()
            ->when($verifiedOnly, function (Builder $query) use ($includeLocalDirectory) {
                $query->where(function (Builder $sourceQuery) use ($includeLocalDirectory) {
                    $sourceQuery->where('source', 'like', 'https://www.travelorientalmindoro.ph/%');

                    if ($includeLocalDirectory) {
                        $sourceQuery->orWhere('source', 'MinTour directory; confirm current details locally');
                    }
                });
            });
    }

    /** Split the query into meaningful terms (drop stopwords and short tokens). */
    private function extractTerms(string $query): array
    {
        $stopwords = [
            'the', 'a', 'an', 'is', 'are', 'was', 'were', 'do', 'does', 'did', 'of', 'to', 'for', 'in', 'on', 'at',
            'by', 'with', 'and', 'or', 'but', 'what', 'where', 'when', 'who', 'why', 'how', 'can', 'could', 'would',
            'should', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'my', 'your', 'our', 'their',
            'ba', 'po', 'opo', 'ang', 'ng', 'sa', 'na', 'ay', 'mga', 'at', 'kung', 'ano', 'saan', 'kailan', 'paano',
            'gusto', 'ko', 'mo', 'niya', 'namin', 'natin', 'ninyo', 'nila', 'may', 'meron', 'wala', 'dito', 'diyan',
            'puwede', 'pwedeng', 'puwedeng', 'nasa', 'near', 'best', 'tell', 'about', 'listed', 'places',
            'which', 'there', 'here', 'get', 'anong',
            'things', 'thing',
            'victoria', 'oriental', 'mindoro',
        ];

        $tokens = preg_split('/[\s\p{P}]+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens, function ($t) use ($stopwords) {
            return mb_strlen($t) >= 3 && ! in_array($t, $stopwords, true);
        }));
    }

    private function expandTerms(array $terms): array
    {
        $synonyms = [
            'eat' => ['eating', 'dining', 'restaurant', 'food', 'kainan', 'kumain'],
            'eating' => ['eat', 'dining', 'restaurant', 'food', 'kainan'],
            'dining' => ['eat', 'restaurant', 'food', 'kainan'],
            'kumain' => ['kainan', 'restaurant', 'food', 'eat'],
            'kain' => ['kainan', 'restaurant', 'food', 'eat'],
            'kainan' => ['restaurant', 'food', 'dining', 'eat'],
            'restaurant' => ['kainan', 'food', 'dining', 'eat'],
            'restaurants' => ['restaurant', 'kainan', 'food', 'dining'],
            'food' => ['restaurant', 'kainan', 'eat'],
            'breakfast' => ['meal', 'food', 'restaurant', 'eat'],
            'lunch' => ['meal', 'food', 'restaurant', 'eat'],
            'dinner' => ['meal', 'food', 'restaurant', 'eat'],
            'meal' => ['food', 'restaurant', 'eat'],
            'meals' => ['meal', 'food', 'restaurant', 'eat'],
            'makakainan' => ['kainan', 'restaurant', 'food', 'eat'],
            'gagawin' => ['activity', 'activities', 'attraction', 'destination', 'visit'],
            'gawin' => ['activity', 'activities', 'attraction', 'destination', 'visit'],
            'activity' => ['activities', 'attraction', 'destination', 'visit'],
            'activities' => ['activity', 'attraction', 'destination', 'visit'],
            'attraction' => ['activities', 'destination', 'visit', 'places'],
            'attractions' => ['attraction', 'destination', 'visit', 'places'],
            'destination' => ['attraction', 'activities', 'visit', 'places'],
            'destinations' => ['destination', 'attraction', 'activities', 'visit'],
            'visit' => ['destination', 'attraction', 'activities', 'gala'],
            'gala' => ['visit', 'destination', 'attraction', 'activities'],
            'sakay' => ['transport', 'route', 'travel', 'pamasahe'],
            'pumunta' => ['directions', 'route', 'travel', 'transport'],
            'papunta' => ['directions', 'route', 'travel', 'transport'],
            'pagpunta' => ['directions', 'route', 'travel', 'transport'],
            'direksyon' => ['directions', 'route', 'travel', 'transport'],
            'pamasahe' => ['fare', 'transport', 'route', 'cost'],
            'biyahe' => ['travel', 'transport', 'route', 'directions'],
            'commute' => ['transport', 'route', 'travel', 'fare'],
            'directions' => ['route', 'travel', 'transport'],
            'route' => ['directions', 'travel', 'transport'],
            'transport' => ['route', 'travel', 'fare'],
            'upcoming' => ['festival', 'event', 'darating'],
            'festivals' => ['festival', 'event', 'pista'],
            'events' => ['event', 'festival', 'pista'],
            'darating' => ['festival', 'event', 'upcoming'],
            'hotel' => ['accommodation', 'lodging', 'hotels'],
            'hotels' => ['hotel', 'accommodation', 'lodging'],
            'price' => ['prices', 'rate', 'rates', 'cost', 'fee'],
            'prices' => ['price', 'rate', 'rates', 'cost', 'fee'],
            'rate' => ['rates', 'price', 'cost', 'fee'],
            'rates' => ['rate', 'price', 'cost', 'fee'],
            'talon' => ['falls', 'waterfall'],
            'falls' => ['talon', 'waterfall'],
            'waterfall' => ['talon', 'falls'],
            'dagat' => ['beach', 'baybayin'],
            'beach' => ['beaches', 'dagat', 'baybayin'],
            'beaches' => ['beach', 'dagat', 'baybayin'],
            'baybayin' => ['dagat', 'beach'],
            'pagkain' => ['restaurant', 'food'],
            'resorts' => ['resort', 'accommodation'],
            'resort' => ['accommodation', 'hotel'],
            'tuluyan' => ['accommodation', 'hotel', 'resort'],
            'matutuluyan' => ['accommodation', 'hotel', 'resort'],
            'pista' => ['festival', 'event'],
            'festival' => ['pista', 'event'],
            'presyo' => ['price', 'cost', 'fee'],
            'presyuhan' => ['price', 'cost', 'fee'],
            'bayad' => ['price', 'cost', 'fee'],
            'gastos' => ['price', 'cost', 'fee'],
            'entrance' => ['entrance fee', 'admission'],
        ];

        foreach ($terms as $term) {
            foreach ($synonyms[$term] ?? [] as $synonym) {
                foreach (explode(' ', $synonym) as $synonymTerm) {
                    if (! in_array($synonymTerm, $terms, true)) {
                        $terms[] = $synonymTerm;
                    }
                }
            }
        }

        return $terms;
    }

    /** Weighted relevance score combining several signals. */
    private function score(KnowledgeArticle $a, string $rawQuery, array $terms): int
    {
        $score = 0;
        $title = mb_strtolower($a->title);
        $content = mb_strtolower($a->content);
        $query = mb_strtolower(trim($rawQuery));

        // Exact phrase in title — very strong signal.
        if ($query !== '' && str_contains($title, $query)) {
            $score += 12;
        }

        // Exact phrase anywhere in content.
        if ($query !== '' && str_contains($content, $query)) {
            $score += 6;
        }

        // Per-term contributions.
        foreach ($terms as $t) {
            if ($this->containsTerm($title, $t)) {
                $score += 4;
            }
            if ($this->containsTerm($content, $t)) {
                $score += 2;
            }
            if ($a->category && $this->containsTerm(mb_strtolower($a->category), $t)) {
                $score += 3;
            }
            if ($a->tags && collect($a->tags)->contains(fn ($tag) => $this->containsTerm(mb_strtolower($tag), $t))) {
                $score += 3;
            }
        }

        return $score;
    }

    private function containsTerm(string $text, string $term): bool
    {
        return preg_match('/(?<![\pL\pN])'.preg_quote($term, '/').'(?![\pL\pN])/u', $text) === 1;
    }

    /** Shape an article for the AI prompt. */
    private function format(KnowledgeArticle $a): array
    {
        return [
            'id' => $a->id,
            'title' => $a->title,
            'category' => $a->category,
            'content' => trim($a->content),
            'source' => $a->source,
        ];
    }
}
