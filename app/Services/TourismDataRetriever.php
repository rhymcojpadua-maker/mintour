<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Announcement;
use App\Models\Destination;
use App\Models\EmergencyContact;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TourismDataRetriever
{
    public const SOURCE = 'MinTour live database; admin-maintained, confirm time-sensitive details';

    private const MIN_SCORE = 3;

    public function retrieve(string $query, ?int $limit = null): array
    {
        $query = trim($query);

        if ($query === '' || mb_strlen($query) < 2) {
            return [];
        }

        $limit = max(1, $limit ?? (int) config('groq.max_db', 6));
        $terms = $this->expandTerms($this->extractTerms($query));
        $intents = $this->detectIntents($query);
        $normalized = mb_strtolower($query);

        $records = $this->records($intents)
            ->map(function (array $record) use ($terms, $intents, $normalized) {
                $record['score'] = $this->score($record, $terms, $intents, $normalized);

                return $record;
            })
            ->filter(fn (array $record) => $record['score'] >= self::MIN_SCORE)
            ->sortByDesc('score');

        if (in_array('destination', $intents, true)) {
            $records = $records->sortByDesc(fn (array $record) => [
                $record['score'],
                $record['recommendation_score'] ?? 0,
            ]);
        }

        return $records
            ->take($limit)
            ->map(fn (array $record) => $record['passage'])
            ->values()
            ->all();
    }

    private function records(array $intents): Collection
    {
        $types = $this->recordTypes($intents);
        $records = collect();

        if (in_array('destination', $types, true)) {
            $records = $records->concat($this->destinations());
        }
        if (in_array('accommodation', $types, true)) {
            $records = $records->concat($this->accommodations());
        }
        if (in_array('restaurant', $types, true)) {
            $records = $records->concat($this->restaurants());
        }
        if (in_array('event', $types, true)) {
            $records = $records->concat($this->events());
        }
        if (in_array('product', $types, true)) {
            $records = $records->concat($this->products());
        }
        if (in_array('announcement', $types, true)) {
            $records = $records->concat($this->announcements());
        }
        if (in_array('faq', $types, true)) {
            $records = $records->concat($this->faqs());
        }
        if (in_array('emergency', $types, true)) {
            $records = $records->concat($this->emergencyContacts());
        }

        return $records;
    }

    private function recordTypes(array $intents): array
    {
        if ($intents === []) {
            return ['destination', 'accommodation', 'restaurant', 'event', 'product', 'announcement', 'faq', 'emergency'];
        }

        $types = [];

        foreach ($intents as $intent) {
            $types = array_merge($types, match ($intent) {
                'destination', 'nature', 'adventure' => ['destination'],
                'beach' => ['destination', 'accommodation'],
                'food' => ['restaurant', 'product'],
                'lodging' => ['accommodation'],
                'event' => ['event', 'faq'],
                'emergency' => ['emergency', 'faq'],
                'transport', 'office' => ['faq', 'destination', 'emergency'],
                'product' => ['product'],
                'announcement' => ['announcement'],
                default => [],
            });
        }

        return array_values(array_unique($types));
    }

    private function destinations(): Collection
    {
        return Destination::query()
            ->published()
            ->with('category')
            ->get()
            ->map(function (Destination $destination) {
                $content = $this->lines([
                    'Destination: '.$destination->name,
                    'Category: '.($destination->category?->name ?? 'Destination'),
                    'Address: '.$this->valueOrNotListed($destination->address),
                    'Description: '.$destination->short_description.' '.$destination->long_description,
                    'Entrance fee: '.$this->valueOrNotListed($destination->entrance_fee),
                    'Opening hours: '.$this->valueOrNotListed($destination->hours),
                    'Best time to visit: '.$this->valueOrNotListed($destination->best_time),
                    'Distance from Victoria town: '.($destination->distance_km !== null ? $destination->distance_km.' km' : 'not listed'),
                    'Rating: '.$this->rating($destination->rating_avg, $destination->rating_count),
                ]);

                return $this->record(
                    'db-destination-'.$destination->id,
                    'destination',
                    'Destination',
                    $destination->name,
                    $destination->category?->name ?? 'Destination',
                    $content,
                ) + [
                    'recommendation_score' => ($destination->is_featured ? 2 : 0) + ($destination->rating_avg ?? 0),
                ];
            });
    }

    private function accommodations(): Collection
    {
        return Accommodation::query()
            ->published()
            ->get()
            ->map(function (Accommodation $accommodation) {
                $content = $this->lines([
                    'Accommodation: '.$accommodation->name,
                    'Type: '.$accommodation->type,
                    'Address: '.$this->valueOrNotListed($accommodation->address),
                    'Nightly rate: '.$this->money($accommodation->price_per_night),
                    'Contact: '.$this->valueOrNotListed($accommodation->contact),
                    'Amenities: '.(! empty($accommodation->amenities) ? implode(', ', $accommodation->amenities) : 'not listed'),
                    'Description: '.$accommodation->description,
                    'Rating: '.$this->rating($accommodation->rating_avg, null),
                ]);

                return $this->record(
                    'db-accommodation-'.$accommodation->id,
                    'accommodation',
                    'Accommodation',
                    $accommodation->name,
                    $accommodation->type,
                    $content,
                );
            });
    }

    private function restaurants(): Collection
    {
        return Restaurant::query()
            ->published()
            ->get()
            ->map(function (Restaurant $restaurant) {
                $content = $this->lines([
                    'Restaurant: '.$restaurant->name,
                    'Cuisine: '.$restaurant->cuisine,
                    'Price range: '.$this->valueOrNotListed($restaurant->price_range),
                    'Address: '.$this->valueOrNotListed($restaurant->address),
                    'Opening hours: '.$this->valueOrNotListed($restaurant->hours),
                    'Description: '.$restaurant->description,
                    'Rating: '.$this->rating($restaurant->rating_avg, null),
                ]);

                return $this->record(
                    'db-restaurant-'.$restaurant->id,
                    'restaurant',
                    'Restaurant',
                    $restaurant->name,
                    'Restaurant',
                    $content,
                );
            });
    }

    private function events(): Collection
    {
        $today = today();

        return Event::query()
            ->published()
            ->orderBy('starts_at')
            ->get()
            ->map(function (Event $event) use ($today) {
                $when = $event->starts_at?->format('F j, Y g:i A') ?? 'date not listed';

                if ($event->ends_at && ! $event->ends_at->isSameDay($event->starts_at)) {
                    $when .= ' to '.$event->ends_at->format('F j, Y g:i A');
                }

                $content = $this->lines([
                    'Event: '.$event->title,
                    'When: '.$when,
                    'Location: '.$event->location,
                    'Category: '.$this->valueOrNotListed($event->category),
                    'Description: '.$event->description,
                ]);

                $recency = match (true) {
                    $event->ends_at !== null && $event->ends_at->lt($today) => 0,
                    $event->starts_at !== null && $event->starts_at->lte($today) => 2,
                    default => 3,
                };

                return $this->record(
                    'db-event-'.$event->id,
                    'event',
                    'Event',
                    $event->title,
                    $event->category ?? 'Event',
                    $content,
                ) + ['recency_boost' => $recency];
            });
    }

    private function products(): Collection
    {
        return Product::query()
            ->published()
            ->get()
            ->map(function (Product $product) {
                $content = $this->lines([
                    'Local product: '.$product->name,
                    'Maker: '.$this->valueOrNotListed($product->maker),
                    'Price: '.$this->money($product->price),
                    'Description: '.$product->description,
                ]);

                return $this->record(
                    'db-product-'.$product->id,
                    'product',
                    'Product',
                    $product->name,
                    'Local product',
                    $content,
                );
            });
    }

    private function announcements(): Collection
    {
        return Announcement::query()
            ->active()
            ->where(function ($query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->get()
            ->map(function (Announcement $announcement) {
                $content = $this->lines([
                    'Announcement: '.$announcement->title,
                    'Published: '.($announcement->published_at?->format('F j, Y') ?? 'date not listed'),
                    'Priority: '.mb_strtolower($announcement->priority?->value ?? 'normal'),
                    'Details: '.$announcement->body,
                ]);

                return $this->record(
                    'db-announcement-'.$announcement->id,
                    'announcement',
                    'Announcement',
                    $announcement->title,
                    'Announcement',
                    $content,
                );
            });
    }

    private function faqs(): Collection
    {
        return Faq::query()
            ->published()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Faq $faq) {
                $content = $this->lines([
                    'Question: '.$faq->question,
                    'Answer: '.$faq->answer,
                ]);

                return $this->record(
                    'db-faq-'.$faq->id,
                    'faq',
                    'FAQ',
                    $faq->question,
                    $faq->category ?? 'FAQ',
                    $content,
                );
            });
    }

    private function emergencyContacts(): Collection
    {
        return EmergencyContact::query()
            ->orderBy('sort_order')
            ->get()
            ->map(function (EmergencyContact $contact) {
                $content = $this->lines([
                    'Emergency contact: '.$contact->name,
                    'Category: '.$this->valueOrNotListed($contact->category),
                    'Phone: '.$contact->phone,
                ]);

                return $this->record(
                    'db-emergency-'.$contact->id,
                    'emergency',
                    'Emergency contact',
                    $contact->name,
                    $contact->category ?? 'Emergency',
                    $content,
                );
            });
    }

    private function record(string $id, string $type, string $kind, string $title, string $category, string $content): array
    {
        $content = Str::limit(trim($content), 900);

        return [
            'type' => $type,
            'title' => $title.' — live '.mb_strtolower($kind).' record',
            'category' => $category,
            'text' => $title.' '.$category.' '.$content,
            'passage' => [
                'id' => $id,
                'title' => $title.' — live '.mb_strtolower($kind).' record',
                'category' => $category,
                'content' => $content,
                'source' => self::SOURCE,
            ],
        ];
    }

    private function score(array $record, array $terms, array $intents, string $normalized): int
    {
        $score = $record['recency_boost'] ?? 0;
        $title = mb_strtolower($record['title']);
        $category = mb_strtolower($record['category']);
        $text = mb_strtolower($record['text']);

        if ($normalized !== '' && str_contains($title, $normalized)) {
            $score += 12;
        }

        if (mb_strlen($normalized) >= 4 && str_contains($text, $normalized)) {
            $score += 4;
        }

        foreach ($terms as $term) {
            if ($this->containsTerm($title, $term)) {
                $score += 4;
            }

            if ($this->containsTerm($category, $term)) {
                $score += 3;
            }

            if ($this->containsTerm($text, $term)) {
                $score += 1;
            }
        }

        return $score + $this->intentBoost($record['type'], $intents);
    }

    private function intentBoost(string $type, array $intents): int
    {
        $boost = 0;

        foreach ($intents as $intent) {
            $boost += match ($intent) {
                'beach' => match ($type) {
                    'destination' => 6,
                    'accommodation' => 2,
                    default => 0,
                },
                'food' => match ($type) {
                    'restaurant' => 8,
                    'product' => 2,
                    default => 0,
                },
                'lodging' => $type === 'accommodation' ? 8 : 0,
                'nature', 'adventure' => $type === 'destination' ? 5 : 0,
                'destination' => $type === 'destination' ? 8 : 0,
                'event' => match ($type) {
                    'event' => 8,
                    'faq' => 2,
                    default => 0,
                },
                'emergency' => match ($type) {
                    'emergency' => 10,
                    'faq' => 2,
                    default => 0,
                },
                'transport' => match ($type) {
                    'faq' => 5,
                    'destination' => 1,
                    default => 0,
                },
                'office' => match ($type) {
                    'faq' => 4,
                    'emergency' => 1,
                    default => 0,
                },
                'product' => $type === 'product' ? 8 : 0,
                'announcement' => $type === 'announcement' ? 8 : 0,
                default => 0,
            };
        }

        return $boost;
    }

    private function detectIntents(string $query): array
    {
        $query = mb_strtolower($query);
        $patterns = [
            'beach' => '/\b(beach|beaches|dagat|baybayin|cove|shore|seaside)\b/iu',
            'destination' => '/\b(destination|destinations|attraction|attractions|best\s+(?:place|places|destination)|places?\s+to\s+visit|where\s+to\s+(?:go|visit))\b|'
                .'(?:pinakamagandang|magandang)\s+(?:pasyalan|puntahan|lugar)|saan\s+(?:maganda|masarap)\s+(?:mamasyal|pumunta|magbakasyon)/iu',
            'food' => '/\b(eat|eating|food|foods|restaurant|restaurants|cafe|coffee|kainan|kumain|pagkain|makakainan|carinderia|eatery)\b/iu',
            'lodging' => '/\b(hotel|hotels|resort|resorts|accommodation|accommodations|lodging|inn|homestay|tuluyan|matutuluyan|tirahan)\b/iu',
            'nature' => '/\b(falls|waterfall|talon|park|nature|mangrove|lake|river|forest|bundok|gubat)\b/iu',
            'adventure' => '/\b(adventure|kayak|kayaking|rafting|trek|trekking|hike|hiking|dive|diving|activity|activities|gawin|gala)\b/iu',
            'event' => '/\b(event|events|festival|festivals|pista|kapakyanan|upcoming|darating)\b/iu',
            'emergency' => '/\b(emergency|police|hospital|fire|rescue|ambulance|hotline|accident|aksidente|tulong|medical)\b/iu',
            'transport' => '/\b(transport|transportation|commute|bus|ferry|van|jeep|tricycle|route|directions|papunta|pumunta|pagpunta|biyahe|terminal|pamasahe)\b/iu',
            'office' => '/\b(tourism office|tourism|opisina|contact|phone|address|email|hours)\b/iu',
            'product' => '/\b(product|products|souvenir|souvenirs|pasalubong|delicacy|maker)\b/iu',
            'announcement' => '/\b(announcement|announcements|advisory|notice|update|news|balita)\b/iu',
        ];

        $intents = [];

        foreach ($patterns as $intent => $pattern) {
            if (preg_match($pattern, $query) === 1) {
                $intents[] = $intent;
            }
        }

        return $intents;
    }

    private function extractTerms(string $query): array
    {
        $stopwords = [
            'the', 'a', 'an', 'is', 'are', 'was', 'were', 'do', 'does', 'did', 'of', 'to', 'for', 'in', 'on', 'at',
            'by', 'with', 'and', 'or', 'but', 'what', 'where', 'when', 'who', 'why', 'how', 'can', 'could', 'would',
            'should', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'my', 'your', 'our', 'their', 'this', 'that',
            'these', 'those', 'there', 'here', 'tell', 'about', 'please', 'give', 'show', 'find', 'want', 'need',
            'ba', 'po', 'opo', 'ang', 'ng', 'sa', 'na', 'ay', 'mga', 'at', 'kung', 'ano', 'saan', 'kailan', 'paano',
            'gusto', 'ko', 'mo', 'niya', 'namin', 'natin', 'ninyo', 'nila', 'may', 'meron', 'wala', 'dito', 'diyan',
            'puwede', 'pwedeng', 'puwedeng', 'nasa', 'near', 'best', 'listed', 'places', 'place', 'which', 'anong',
            'things', 'thing', 'victoria', 'oriental', 'mindoro', 'philippines', 'pilipinas', 'meron', 'ba',
        ];

        $tokens = preg_split('/[\s\p{P}]+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens, function (string $term) use ($stopwords) {
            return mb_strlen($term) >= 3 && ! in_array($term, $stopwords, true);
        }));
    }

    private function expandTerms(array $terms): array
    {
        $synonyms = [
            'beach' => ['beaches', 'dagat', 'baybayin', 'cove', 'shore'],
            'beaches' => ['beach', 'dagat', 'baybayin', 'cove'],
            'dagat' => ['beach', 'baybayin', 'cove'],
            'baybayin' => ['beach', 'dagat'],
            'food' => ['eat', 'restaurant', 'kainan', 'pagkain', 'cafe'],
            'eat' => ['food', 'restaurant', 'kainan', 'pagkain'],
            'kain' => ['food', 'restaurant', 'kainan', 'pagkain'],
            'kumain' => ['food', 'restaurant', 'kainan', 'pagkain'],
            'kainan' => ['food', 'restaurant', 'pagkain', 'cafe'],
            'pagkain' => ['food', 'restaurant', 'kainan'],
            'restaurant' => ['food', 'eat', 'kainan', 'cafe'],
            'restaurants' => ['restaurant', 'food', 'kainan', 'cafe'],
            'hotel' => ['accommodation', 'lodging', 'resort', 'tuluyan'],
            'hotels' => ['hotel', 'accommodation', 'lodging', 'resort'],
            'resort' => ['hotel', 'accommodation', 'lodging'],
            'resorts' => ['resort', 'hotel', 'accommodation'],
            'tuluyan' => ['hotel', 'resort', 'accommodation'],
            'matutuluyan' => ['hotel', 'resort', 'accommodation'],
            'falls' => ['waterfall', 'talon'],
            'waterfall' => ['falls', 'talon'],
            'talon' => ['falls', 'waterfall'],
            'festival' => ['event', 'pista'],
            'festivals' => ['festival', 'event', 'pista'],
            'event' => ['festival', 'pista'],
            'events' => ['event', 'festival', 'pista'],
            'pista' => ['festival', 'event'],
            'emergency' => ['police', 'hospital', 'fire', 'rescue', 'hotline', 'ambulance'],
            'police' => ['emergency'],
            'hospital' => ['emergency', 'medical'],
            'souvenir' => ['product', 'pasalubong'],
            'souvenirs' => ['souvenir', 'product', 'pasalubong'],
            'pasalubong' => ['product', 'souvenir'],
            'commute' => ['transport', 'route', 'bus', 'ferry', 'van', 'tricycle'],
            'papunta' => ['directions', 'route', 'transport'],
            'pumunta' => ['directions', 'route', 'transport'],
            'pagpunta' => ['directions', 'route', 'transport'],
            'price' => ['rate', 'cost', 'fee', 'presyo', 'magkano'],
            'presyo' => ['price', 'rate', 'cost', 'fee'],
            'magkano' => ['price', 'rate', 'cost', 'fee'],
            'entrance' => ['fee', 'admission'],
            'contact' => ['phone', 'address', 'email', 'office'],
            'phone' => ['contact', 'number'],
            'address' => ['location', 'contact'],
            'activity' => ['activities', 'destination', 'adventure'],
            'activities' => ['activity', 'destination', 'adventure'],
            'gawin' => ['activity', 'activities', 'destination'],
            'gala' => ['activity', 'activities', 'destination'],
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

    private function lines(array $lines): string
    {
        return implode("\n", array_filter($lines, function (string $line) {
            $value = trim(Str::after($line, ':'));

            return $value !== '' && mb_strtolower($value) !== 'null';
        }));
    }

    private function valueOrNotListed(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' || mb_strtolower($value) === 'null' ? 'not listed' : $value;
    }

    private function money(?float $amount): string
    {
        return $amount === null ? 'not listed' : 'PHP '.number_format($amount, 2);
    }

    private function rating(?float $rating, ?int $count): string
    {
        if ($rating === null || $rating <= 0) {
            return 'not listed';
        }

        return number_format($rating, 1).' / 5'.($count !== null && $count > 0 ? " from {$count} reviews" : '');
    }

    private function containsTerm(string $text, string $term): bool
    {
        return preg_match('/(?<![\pL\pN])'.preg_quote($term, '/').'(?![\pL\pN])/u', $text) === 1;
    }
}
