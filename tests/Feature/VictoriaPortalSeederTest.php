<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\Event;
use App\Models\Faq;
use App\Models\KnowledgeArticle;
use App\Models\Restaurant;
use App\Support\MediaUrl;
use Database\Seeders\VictoriaDemoSeeder;
use Database\Seeders\VictoriaPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class VictoriaPortalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_dataset_imports_into_existing_tourism_sections_idempotently(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 25)->startOfDay());

        $seeder = app(VictoriaPortalSeeder::class);
        $seeder->run();

        $this->assertDatabaseCount('destinations', 7);
        $this->assertDatabaseCount('accommodations', 10);
        $this->assertDatabaseCount('restaurants', 8);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('destination_photos', 7);

        $falls = Destination::where('slug', 'mabalbay-falls')->firstOrFail();
        $this->assertSame('villa cerveza, victoria, Oriental Mindoro', $falls->address);
        $this->assertSame('Free - no entrance fee', $falls->entrance_fee);
        $this->assertEqualsWithDelta(13.12205852, $falls->latitude, 0.0000001);
        $this->assertStringContainsString('40 minutes of trekking', $falls->long_description);

        $resort = Accommodation::where('slug', 'matala-resort-and-catering-services')->firstOrFail();
        $this->assertStringNotContainsString('free to enter', strtolower($resort->description));
        $this->assertSame('/images/accommodations/matala-resort.png', $resort->cover_image);
        $restaurant = Restaurant::where('slug', 'bradz-restaurant')->firstOrFail();
        $this->assertStringNotContainsString('free to enter', strtolower($restaurant->description));

        $this->assertDatabaseHas('accommodations', [
            'slug' => 'matala-resort-and-catering-services',
            'price_per_night' => null,
            'is_published' => true,
        ]);
        $this->assertDatabaseMissing('accommodations', ['slug' => 'atienzas-beach-resort']);
        $this->assertDatabaseMissing('accommodations', ['slug' => 'eighty-eight-hotel-and-event-center']);
        $this->assertDatabaseMissing('knowledge_articles', [
            'source' => 'https://www.travelorientalmindoro.ph/place/atienzas-beach-resort',
        ]);
        $this->assertDatabaseMissing('knowledge_articles', [
            'source' => 'https://www.travelorientalmindoro.ph/place/eighty-eight-hotel-and-event-center',
        ]);
        $this->assertDatabaseHas('restaurants', [
            'slug' => 'grounded-cafe-64d2a571',
            'cuisine' => 'Cafe & Coffee Shop',
            'is_published' => true,
        ]);
        $this->assertDatabaseHas('events', [
            'slug' => 'kapakyanan-festival',
            'starts_at' => '2027-09-18 00:00:00',
        ]);
        $this->assertDatabaseHas('faqs', [
            'question' => 'How can I contact the Victoria Municipal Tourism Office?',
        ]);
        $this->assertDatabaseHas('destinations', [
            'slug' => 'victoria-v-two-trading',
            'name' => 'Victoria V. Two Trading',
        ]);
        $trading = Destination::where('slug', 'victoria-v-two-trading')->firstOrFail();
        $this->assertEqualsWithDelta(13.1732875, $trading->latitude, 0.0000001);
        $this->assertEqualsWithDelta(121.278265625, $trading->longitude, 0.0000001);
        $this->assertTrue(KnowledgeArticle::where('title', 'Victoria V. Two Trading — official tourism listing')->exists());

        $this->getJson('/api/map/features')
            ->assertOk()
            ->assertJsonCount(23, 'features')
            ->assertJsonFragment(['name' => 'Mabalbay Falls'])
            ->assertJsonFragment(['name' => 'Matala Resort And Catering Services'])
            ->assertJsonFragment(['name' => 'Bradz Restaurant'])
            ->assertJsonFragment(['name' => "Victoria's Bed and Breakfast"]);
        $tradingCard = Blade::render(
            '<x-public.destination-card :destination="$destination" />',
            ['destination' => Destination::where('slug', 'victoria-v-two-trading')->firstOrFail()],
        );
        $this->assertStringContainsString('Open in Google Maps', $tradingCard);
        $this->assertStringContainsString('destination=13.1732875,121.278265625', $tradingCard);

        $seeder->run();

        $this->assertDatabaseCount('destinations', 7);
        $this->assertDatabaseCount('accommodations', 10);
        $this->assertDatabaseCount('restaurants', 8);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('knowledge_articles', 28);

        $this->travelBack();
    }

    public function test_demo_seed_removes_wandel_food_park_and_updates_food_references(): void
    {
        Restaurant::create([
            'name' => 'Wandel Food Park',
            'slug' => 'wandel-food-park',
            'cuisine' => 'Food court',
            'description' => 'Old listing.',
            'is_published' => true,
        ]);

        Artisan::call('db:seed', ['--class' => VictoriaDemoSeeder::class]);

        $this->assertDatabaseMissing('restaurants', ['slug' => 'wandel-food-park']);
        $this->assertStringNotContainsString(
            'Wandel Food Park',
            Faq::where('question', 'Where can I find places to eat in Victoria?')->value('answer'),
        );
        $this->assertStringNotContainsString(
            'Wandel Food Park',
            KnowledgeArticle::where('title', 'Where to eat local delicacies')->value('content'),
        );
    }

    public function test_demo_seed_removes_the_two_requested_hotel_listings_and_updates_accommodation_references(): void
    {
        foreach ([
            ['EightyEight Hotel And Restaurant', 'eightyeight-hotel-and-restaurant'],
            ['RIZ Diamond Hotel & Restaurant', 'riz-diamond-hotel-restaurant'],
        ] as [$name, $slug]) {
            Accommodation::create([
                'name' => $name,
                'slug' => $slug,
                'type' => 'Hotel',
                'address' => 'Victoria, Oriental Mindoro',
                'description' => 'Listing to be removed.',
                'is_published' => true,
            ]);
        }

        Artisan::call('db:seed', ['--class' => VictoriaDemoSeeder::class]);

        $this->assertDatabaseMissing('accommodations', ['slug' => 'eightyeight-hotel-and-restaurant']);
        $this->assertDatabaseMissing('accommodations', ['slug' => 'riz-diamond-hotel-restaurant']);
        $this->assertStringNotContainsString(
            'EightyEight Hotel And Restaurant',
            Faq::where('question', 'Are there accommodations in Victoria?')->value('answer'),
        );
        $this->assertStringNotContainsString(
            'RIZ Diamond Hotel & Restaurant',
            Faq::where('question', 'Are there accommodations in Victoria?')->value('answer'),
        );
        $accommodationArticle = KnowledgeArticle::where('title', 'Recommended accommodations and price ranges')->value('content');
        $this->assertStringNotContainsString('EightyEight Hotel And Restaurant', $accommodationArticle);
        $this->assertStringNotContainsString('RIZ Diamond Hotel & Restaurant', $accommodationArticle);
    }

    public function test_user_supplied_maxine_photo_is_used_as_a_public_asset(): void
    {
        app(VictoriaPortalSeeder::class)->run();

        $photoPath = '/images/restaurants/maxines-restaurant.png';

        $this->assertSame($photoPath, Restaurant::where('slug', 'maxines-restaurant')->value('cover_image'));
        $this->assertSame($photoPath, MediaUrl::resolve($photoPath));
        $this->assertFileExists(public_path($photoPath));
    }

    public function test_municipal_hall_photo_uses_a_public_path_even_for_old_local_urls(): void
    {
        $photoPath = '/images/victoria-municipality.jpg';

        $this->assertSame($photoPath, MediaUrl::resolve('http://127.0.0.1:8000/images/victoria-municipality.jpg'));
        $this->assertSame($photoPath, MediaUrl::resolve('http://mintour.test/images/victoria-municipality.jpg'));

        Artisan::call('db:seed', ['--class' => VictoriaDemoSeeder::class]);

        $this->assertSame($photoPath, Destination::where('slug', 'victoria-municipal-hall-plaza')->value('cover_image'));
        $this->assertFileExists(public_path($photoPath));
    }

    public function test_user_supplied_matala_resort_photo_is_used_for_the_portal_listing(): void
    {
        $photoPath = '/images/accommodations/matala-resort.png';

        app(VictoriaPortalSeeder::class)->run();
        $this->assertSame($photoPath, Accommodation::where('slug', 'matala-resort-and-catering-services')->value('cover_image'));
        $this->assertDatabaseMissing('accommodations', ['slug' => 'matala-resort-hotel']);
        $this->assertFileExists(public_path($photoPath));
        $this->assertSame($photoPath, \App\Support\MediaUrl::resolve($photoPath));
    }

    public function test_demo_seed_removes_duplicate_matala_map_listing_and_keeps_portal_accommodation(): void
    {
        app(VictoriaPortalSeeder::class)->run();

        Accommodation::create([
            'name' => 'MATALA Resort Hotel',
            'slug' => 'matala-resort-hotel',
            'type' => 'Resort',
            'address' => 'Victoria, Oriental Mindoro',
            'description' => 'Duplicate map listing.',
            'is_published' => true,
        ]);

        Artisan::call('db:seed', ['--class' => VictoriaDemoSeeder::class]);

        $this->assertDatabaseMissing('accommodations', ['slug' => 'matala-resort-hotel']);
        $this->assertDatabaseHas('accommodations', ['slug' => 'matala-resort-and-catering-services']);
        $this->assertStringContainsString(
            'Matala Resort And Catering Services',
            Faq::where('question', 'Are there accommodations in Victoria?')->value('answer'),
        );
        $this->assertStringContainsString(
            'Matala Resort And Catering Services',
            KnowledgeArticle::where('title', 'Recommended accommodations and price ranges')->value('content'),
        );
    }

    public function test_stay_cards_do_not_use_a_generic_stock_photo_for_unpictured_listings(): void
    {
        $view = file_get_contents(resource_path('views/public/accommodations/index.blade.php'));

        $this->assertStringNotContainsString('photo-1571003123894-1f0594d2b5d9', $view);
        $this->assertStringContainsString('MediaUrl::resolve($a->cover_image)', $view);
        $this->assertStringContainsString('bg-linear-to-br from-teal-50', $view);
    }
}
