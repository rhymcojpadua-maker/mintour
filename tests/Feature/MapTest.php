<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\Restaurant;
use App\Http\Controllers\MapController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_page_receives_active_categories_and_destination_coordinates(): void
    {
        $category = Category::create([
            'name' => 'Waterfalls',
            'slug' => 'waterfalls',
            'color' => '#0f766e',
            'is_active' => true,
        ]);
        $this->createDestination($category, 'Maalig Falls', 'maalig-falls');

        $view = app(MapController::class)->index();
        $data = $view->getData();

        $this->assertSame('public.map', $view->name());
        $this->assertSame(['waterfalls'], $data['categories']->pluck('slug')->all());
        $this->assertSame('Maalig Falls', $data['initial'][0]['name']);
        $this->assertSame(13.1733849, $data['initial'][0]['lat']);
        $this->assertSame(121.2787746, $data['initial'][0]['lng']);
        $this->assertStringNotContainsString(
            '4.5 Generic list-page pattern (accommodations)',
            file_get_contents(resource_path('views/public/map.blade.php'))
        );
    }

    public function test_map_api_returns_only_published_destinations_in_active_categories(): void
    {
        $activeCategory = Category::create([
            'name' => 'Waterfalls',
            'slug' => 'waterfalls',
            'color' => '#0f766e',
            'is_active' => true,
        ]);
        $otherCategory = Category::create([
            'name' => 'Viewpoints',
            'slug' => 'viewpoints',
            'color' => '#2563eb',
            'is_active' => true,
        ]);
        $inactiveCategory = Category::create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'color' => '#64748b',
            'is_active' => false,
        ]);

        $this->createDestination($activeCategory, 'Maalig Falls', 'maalig-falls');
        $this->createDestination($otherCategory, 'Mountain View', 'mountain-view');
        $this->createDestination($inactiveCategory, 'Unlisted Falls', 'unlisted-falls');
        $this->createDestination($activeCategory, 'Draft Falls', 'draft-falls', false);

        $this->getJson(route('api.map.features'))
            ->assertOk()
            ->assertJsonCount(2, 'features')
            ->assertJsonFragment(['name' => 'Maalig Falls'])
            ->assertJsonFragment(['name' => 'Mountain View'])
            ->assertJsonMissing(['name' => 'Unlisted Falls'])
            ->assertJsonMissing(['name' => 'Draft Falls']);

        $this->getJson(route('api.map.features') . '?categories%5B%5D=waterfalls')
            ->assertOk()
            ->assertJsonCount(1, 'features')
            ->assertJsonFragment(['name' => 'Maalig Falls'])
            ->assertJsonMissing(['name' => 'Mountain View']);
    }

    public function test_map_includes_geocoded_accommodations_and_restaurants_and_filters_them(): void
    {
        $category = Category::create([
            'name' => 'Nature',
            'slug' => 'nature',
            'color' => '#0f766e',
            'is_active' => true,
        ]);
        $this->createDestination($category, 'Mabalbay Falls', 'mabalbay-falls');

        Accommodation::create([
            'name' => 'Victoria Resort',
            'slug' => 'victoria-resort',
            'type' => 'Resort',
            'price_per_night' => 0,
            'address' => 'Victoria, Oriental Mindoro',
            'latitude' => 13.18,
            'longitude' => 121.28,
            'description' => 'A mapped resort.',
            'is_published' => true,
        ]);
        Accommodation::create([
            'name' => 'Unmapped Stay',
            'slug' => 'unmapped-stay',
            'type' => 'Hotel',
            'price_per_night' => 0,
            'description' => 'No source coordinates.',
            'is_published' => true,
        ]);
        Restaurant::create([
            'name' => 'Victoria Cafe',
            'slug' => 'victoria-cafe',
            'cuisine' => 'Cafe',
            'address' => 'Victoria, Oriental Mindoro',
            'latitude' => 13.19,
            'longitude' => 121.27,
            'description' => 'A mapped cafe.',
            'is_published' => true,
        ]);

        $this->getJson(route('api.map.features'))
            ->assertOk()
            ->assertJsonCount(3, 'features')
            ->assertJsonFragment(['name' => 'Victoria Resort', 'type' => 'accommodation'])
            ->assertJsonFragment(['name' => 'Victoria Cafe', 'type' => 'restaurant'])
            ->assertJsonMissing(['name' => 'Unmapped Stay']);

        $this->getJson(route('api.map.features').'?categories%5B%5D=accommodation')
            ->assertOk()
            ->assertJsonCount(1, 'features')
            ->assertJsonFragment(['name' => 'Victoria Resort'])
            ->assertJsonMissing(['name' => 'Victoria Cafe'])
            ->assertJsonMissing(['name' => 'Mabalbay Falls']);
    }

    private function createDestination(Category $category, string $name, string $slug, bool $published = true): void
    {
        Destination::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'short_description' => "{$name} in Victoria.",
            'long_description' => "{$name} is a destination in Victoria.",
            'latitude' => 13.1733849,
            'longitude' => 121.2787746,
            'is_published' => $published,
        ]);
    }
}
