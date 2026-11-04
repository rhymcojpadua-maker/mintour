<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Category;
use App\Models\Destination;
use App\Models\DestinationPhoto;
use App\Models\Event;
use App\Models\Faq;
use App\Models\KnowledgeArticle;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class VictoriaPortalSeeder extends Seeder
{
    private const SOURCE = 'https://www.travelorientalmindoro.ph';

    private const EXCLUDED_PLACE_SLUGS = [
        'atienzas-beach-resort',
        'eighty-eight-hotel-and-event-center',
    ];

    public function run(): void
    {
        $datasetPath = database_path('../victoria_dataset.json');
        if (! is_file($datasetPath)) {
            throw new RuntimeException("Victoria tourism dataset not found: {$datasetPath}");
        }

        $dataset = json_decode(file_get_contents($datasetPath), true, 512, JSON_THROW_ON_ERROR);
        if (count($dataset['places'] ?? []) !== 27) {
            throw new RuntimeException('Expected 27 Victoria place listings in victoria_dataset.json.');
        }

        $this->seedCategories();

        foreach ($dataset['places'] as $place) {
            $slug = basename(parse_url($place['url'], PHP_URL_PATH));
            if (in_array($slug, self::EXCLUDED_PLACE_SLUGS, true)) {
                Accommodation::where('slug', $slug)->delete();
                KnowledgeArticle::where('source', $place['url'])->delete();

                continue;
            }

            $this->seedPlace($place);
            $this->seedPlaceKnowledge($place);
        }

        $this->seedMunicipalityKnowledge($dataset);
        $this->seedTourismOffice($dataset['tourism_office']);
        $this->seedFestival($dataset['festivals'][0] ?? []);

        $this->command?->info('Victoria tourism portal data imported.');
    }

    private function seedCategories(): void
    {
        foreach ([
            ['name' => 'Nature', 'slug' => 'nature', 'icon' => '🌿', 'color' => '#0f766e', 'sort_order' => 2],
            ['name' => 'Adventure', 'slug' => 'adventure', 'icon' => '⛰️', 'color' => '#8b5cf6', 'sort_order' => 5],
            ['name' => 'Shopping', 'slug' => 'shopping', 'icon' => '🛍️', 'color' => '#d97706', 'sort_order' => 6],
        ] as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true],
            );
        }
    }

    private function seedPlace(array $place): void
    {
        $slug = basename(parse_url($place['url'], PHP_URL_PATH));
        $description = trim($place['description'] ?? '');
        $address = $place['address']['streetAddress'] ?? 'Victoria, Oriental Mindoro';
        $latitude = $place['geo']['latitude'] ?? null;
        $longitude = $place['geo']['longitude'] ?? null;
        $contact = implode(' | ', array_filter([
            $place['telephone'] ?? null,
            $place['email'] ?? null,
        ]));
        $details = $this->placeDetails($place);

        if (in_array($place['category'], ['attraction', 'shopping'], true)) {
            if ($latitude === null || $longitude === null) {
                throw new RuntimeException("Missing map coordinates for attraction: {$place['name']}");
            }

            $categorySlug = match ($place['category']) {
                'shopping' => 'shopping',
                default => Str::contains(Str::lower($place['name']), 'creek') ? 'adventure' : 'nature',
            };
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $fee = $place['category'] === 'attraction'
                ? $this->entranceFee($place)
                : null;

            $destination = Destination::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $place['name'],
                    'tagline' => $category->name,
                    'short_description' => Str::limit($description, 250),
                    'long_description' => $details,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'address' => $address,
                    'entrance_fee' => $fee,
                    'rating_avg' => data_get($place, 'rating.ratingValue', 0),
                    'rating_count' => data_get($place, 'rating.ratingCount', 0),
                    'cover_image' => $place['image'] ?? null,
                    'is_featured' => false,
                    'is_published' => true,
                ],
            );

            if (! empty($place['image'])) {
                DestinationPhoto::updateOrCreate(
                    ['destination_id' => $destination->id, 'sort_order' => 0],
                    ['path' => $place['image'], 'caption' => $place['name'].' — Travel Oriental Mindoro'],
                );
            }

            return;
        }

        if (in_array($place['category'], ['resort', 'hotel'], true)) {
            Accommodation::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $place['name'],
                    'type' => Str::title($place['category']),
                    'price_per_night' => null,
                    'rating_avg' => data_get($place, 'rating.ratingValue', 0),
                    'address' => $address,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'contact' => $contact ?: null,
                    'description' => $details,
                    'amenities' => [],
                    'cover_image' => $slug === 'matala-resort-and-catering-services'
                        ? '/images/accommodations/matala-resort.png'
                        : ($place['image'] ?? null),
                    'is_published' => true,
                ],
            );

            return;
        }

        if (in_array($place['category'], ['restaurant', 'cafe_coffee_shop'], true)) {
            Restaurant::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $place['name'],
                    'cuisine' => $place['category'] === 'cafe_coffee_shop' ? 'Cafe & Coffee Shop' : 'Restaurant',
                    'rating_avg' => data_get($place, 'rating.ratingValue', 0),
                    'address' => $address,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'description' => $details,
                    'cover_image' => $slug === 'maxines-restaurant'
                        ? '/images/restaurants/maxines-restaurant.png'
                        : ($place['image'] ?? null),
                    'is_published' => true,
                ],
            );
        }
    }

    private function placeDetails(array $place): string
    {
        $details = array_filter([
            trim($place['description'] ?? ''),
            'Address: '.($place['address']['streetAddress'] ?? 'Victoria, Oriental Mindoro'),
            isset($place['telephone']) ? 'Phone: '.$place['telephone'] : null,
            isset($place['email']) ? 'Email: '.$place['email'] : null,
            ! empty($place['geo']) ? 'Coordinates: '.$place['geo']['latitude'].', '.$place['geo']['longitude'] : null,
            $place['location_note'] ?? null,
            ! empty($place['dateModified']) ? 'Portal listing updated: '.$place['dateModified'].'.' : null,
        ]);

        foreach ($place['faq'] ?? [] as $faq) {
            $question = Str::lower($faq['question']);
            $isFeeQuestion = Str::contains($question, ['cost', 'entrance fee', 'price', 'admission']);

            if ($isFeeQuestion && $place['category'] !== 'attraction') {
                continue;
            }

            if ($isFeeQuestion) {
                $details[] = (Str::contains($question, ['entrance', 'admission'])
                    || Str::contains(Str::lower($faq['answer']), 'free to enter')
                    ? 'Entrance fee'
                    : 'Listed cost information; entrance fee not confirmed').': '.$faq['answer'];
                continue;
            }

            $details[] = $faq['question'].': '.$faq['answer'];
        }

        $details[] = 'Source: '.$place['url'];

        return implode("\n\n", $details);
    }

    private function entranceFee(array $place): ?string
    {
        foreach ($place['faq'] ?? [] as $faq) {
            $question = Str::lower($faq['question']);
            if (Str::contains($question, ['entrance fee', 'entrance', 'admission'])
                || Str::contains(Str::lower($faq['answer']), 'free to enter')) {
                return Str::contains(Str::lower($faq['answer']), 'free to enter')
                    ? 'Free - no entrance fee'
                    : $faq['answer'];
            }
        }

        return null;
    }

    private function seedPlaceKnowledge(array $place): void
    {
        KnowledgeArticle::updateOrCreate(
            ['title' => $place['name'].' — official tourism listing'],
            [
                'category' => Str::title(str_replace('_', ' ', $place['category'])),
                'content' => $this->placeDetails($place),
                'source' => $place['url'],
                'tags' => ['Victoria', $place['category'], $place['name'], 'tourism'],
                'is_published' => true,
            ],
        );
    }

    private function seedMunicipalityKnowledge(array $dataset): void
    {
        $municipality = $dataset['municipality'];
        $counts = collect($municipality['destination_counts'])
            ->map(fn ($count, $type) => Str::title(str_replace('_', ' ', $type)).": {$count}")
            ->implode('; ');

        KnowledgeArticle::updateOrCreate(
            ['title' => 'Victoria, Oriental Mindoro — municipality profile'],
            [
                'category' => 'Municipality',
                'content' => implode("\n\n", [
                    "Victoria is a municipality in {$municipality['province']}. The tourism portal estimates its population at about ".number_format($municipality['population_approx'])." and its land area at roughly ".number_format($municipality['land_area_sq_km_approx'])." square kilometres.",
                    "The portal lists {$municipality['listed_destinations']} destinations. Category counts shown on the portal: {$counts}. Categories can overlap.",
                    'Source: '.$municipality['source_url'],
                ]),
                'source' => $municipality['source_url'],
                'tags' => ['Victoria', 'municipality', 'population', 'land area', 'tourism'],
                'is_published' => true,
            ],
        );
    }

    private function seedTourismOffice(array $office): void
    {
        $content = implode("\n\n", [
            $office['name'],
            'Address: '.$office['address'],
            'Tourism officer listed: '.$office['contact_person'],
            'Phone: '.implode(', ', $office['phone']),
            'Office hours: '.$office['hours'],
            'Source: '.$office['source_url'],
        ]);

        KnowledgeArticle::updateOrCreate(
            ['title' => 'Victoria Municipal Tourism Office — contact information'],
            [
                'category' => 'Tourism Office',
                'content' => $content,
                'source' => $office['source_url'],
                'tags' => ['Victoria', 'tourism office', 'contact', 'phone', 'hours'],
                'is_published' => true,
            ],
        );

        Faq::updateOrCreate(
            ['question' => 'How can I contact the Victoria Municipal Tourism Office?'],
            [
                'answer' => "{$office['name']} is at {$office['address']}. Call ".implode(' or ', $office['phone']).". Listed office hours: {$office['hours']}.",
                'category' => 'Tourism Office',
                'sort_order' => 20,
                'is_published' => true,
            ],
        );
    }

    private function seedFestival(array $festival): void
    {
        if (empty($festival['name']) || empty($festival['annual_month_day'])) {
            throw new RuntimeException('Kapakyanan Festival name and annual date are required in the dataset.');
        }

        [$month, $day] = array_map('intval', explode('-', $festival['annual_month_day']));
        $startsAt = Carbon::createFromDate(now()->year, $month, $day)->startOfDay();
        if ($startsAt->isPast()) {
            $startsAt->addYear();
        }
        $endsAt = $startsAt->copy()->endOfDay();
        $description = $festival['description'].' The portal lists this annual festival on '.$startsAt->format('F j').'; confirm the current programme and venue before travelling.';

        Event::updateOrCreate(
            ['slug' => Str::slug($festival['name'])],
            [
                'title' => $festival['name'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'location' => 'Victoria, Oriental Mindoro (venue to be confirmed)',
                'category' => 'Cultural',
                'description' => $description,
                'is_published' => true,
            ],
        );

        KnowledgeArticle::updateOrCreate(
            ['title' => $festival['name'].' — festival information'],
            [
                'category' => 'Events',
                'content' => $description."\n\nSource: {$festival['source_url']}",
                'source' => $festival['source_url'],
                'tags' => ['Victoria', 'festival', 'Kapakyanan', 'events'],
                'is_published' => true,
            ],
        );
    }
}
