<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function signInAsAdmin(): User
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);

        return $admin;
    }

    public function test_dashboard_displays_without_embedded_documentation(): void
    {
        $this->signInAsAdmin();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('5. Module index pages')
            ->assertDontSee('3. Reusable admin components')
            ->assertDontSee('3. Blade components')
            ->assertSee('Analytics Dashboard');
    }

    public function test_destination_admin_actions_work(): void
    {
        $this->signInAsAdmin();
        $destination = Destination::factory()->create(['is_featured' => false]);

        $this->get(route('admin.destinations.index'))->assertOk();

        $this->patch(route('admin.destinations.toggle-featured', $destination))
            ->assertRedirect();

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'is_featured' => true,
        ]);
    }

    public function test_generic_admin_edit_resolves_slug_keyed_records(): void
    {
        $this->signInAsAdmin();
        $accommodation = Accommodation::create([
            'name' => 'Admin edit test',
            'slug' => 'admin-edit-test',
            'type' => 'Hotel',
            'price_per_night' => 0,
            'address' => 'Victoria',
            'description' => 'Test accommodation listing.',
            'is_published' => true,
        ]);

        $this->get(route('admin.accommodations.edit', $accommodation))
            ->assertOk()
            ->assertSee('Admin edit test')
            ->assertSee('Price per night (PHP)');

        $this->put(route('admin.accommodations.update', $accommodation), [
            'name' => 'Updated accommodation',
            'slug' => 'admin-edit-test',
            'type' => 'Hotel',
            'price_per_night' => 1250,
            'address' => 'Victoria',
            'description' => 'Updated test accommodation listing.',
            'is_published' => 1,
        ])->assertRedirect(route('admin.accommodations.index'));

        $this->assertDatabaseHas('accommodations', [
            'id' => $accommodation->id,
            'slug' => 'admin-edit-test',
            'price_per_night' => 1250,
        ]);
    }

    public function test_accommodation_rate_can_be_left_blank_in_admin(): void
    {
        $this->signInAsAdmin();

        $this->post(route('admin.accommodations.store'), [
            'name' => 'Rate not listed test',
            'type' => 'Hotel',
            'description' => 'Current rates have not been verified.',
            'is_published' => 1,
        ])->assertRedirect(route('admin.accommodations.index'));

        $accommodation = Accommodation::where('slug', 'rate-not-listed-test')->firstOrFail();
        $this->assertNull($accommodation->price_per_night);
    }

    public function test_generic_forms_include_resource_specific_fields_and_can_create_categories(): void
    {
        $this->signInAsAdmin();

        $this->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('Category color')
            ->assertSee('Sort order');

        $this->post(route('admin.categories.store'), [
            'name' => 'Verified places',
            'icon' => '📍',
            'color' => '#0f766e',
            'description' => 'Places checked by the tourism team.',
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'verified-places',
            'color' => '#0f766e',
            'is_active' => true,
        ]);
    }

    public function test_all_admin_content_lists_and_create_forms_load_and_search(): void
    {
        $this->signInAsAdmin();

        foreach ([
            'announcements',
            'categories',
            'accommodations',
            'restaurants',
            'products',
            'events',
            'faqs',
            'emergency-contacts',
            'knowledge',
            'users',
        ] as $resource) {
            $this->get(route('admin.'.$resource.'.index'))
                ->assertOk();
            $this->get(route('admin.'.$resource.'.index', ['q' => 'search-check']))
                ->assertOk();
            $this->get(route('admin.'.$resource.'.create'))
                ->assertOk();
        }

        foreach (['reviews', 'unanswered', 'settings'] as $section) {
            $route = match ($section) {
                'settings' => 'admin.settings.edit',
                default => 'admin.'.$section.'.index',
            };
            $this->get(route($route))->assertOk();
        }
    }

    public function test_settings_are_validated_saved_and_rendered(): void
    {
        $this->signInAsAdmin();
        foreach (['site_name', 'contact_email', 'default_lang', 'groq_model', 'rate_limit', 'max_kb'] as $key) {
            Cache::forget("setting.$key");
        }

        $this->put(route('admin.settings.update'), [
            'settings' => [
                'site_name' => 'Victoria Visitor Guide',
                'contact_email' => 'hello@example.com',
                'default_lang' => 'fil',
                'groq_model' => 'openai/gpt-oss-20b',
                'rate_limit' => 31,
                'max_kb' => 7,
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Victoria Visitor Guide']);
        $this->assertDatabaseHas('settings', ['key' => 'groq_model', 'value' => 'openai/gpt-oss-20b']);
        $this->assertDatabaseHas('settings', ['key' => 'rate_limit', 'value' => '31']);
        $this->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Victoria Visitor Guide')
            ->assertSee('hello@example.com')
            ->assertSee('Groq API key')
            ->assertSee('GROQ_API_KEY');
    }

    public function test_user_management_is_restricted_to_super_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.users.create'))->assertForbidden();
    }

    public function test_destination_gallery_photo_can_be_deleted_from_its_own_record(): void
    {
        $this->signInAsAdmin();
        $destination = Destination::factory()->create();
        $photo = $destination->photos()->create([
            'path' => 'destinations/gallery/test-photo.jpg',
            'caption' => 'Test photo',
            'sort_order' => 0,
        ]);

        $this->delete(route('admin.destinations.photos.destroy', [$destination, $photo]))
            ->assertRedirect();

        $this->assertDatabaseMissing('destination_photos', ['id' => $photo->id]);
    }
}
