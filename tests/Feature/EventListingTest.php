<?php

namespace Tests\Feature;

use App\Http\Controllers\EventController;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_upcoming_events_are_listed_before_past_events(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 4)->setTime(12, 0));

        Event::create([
            'title' => 'Past town activity',
            'slug' => 'past-town-activity',
            'starts_at' => '2026-09-20 00:00:00',
            'location' => 'Victoria',
            'description' => 'Past event.',
            'is_published' => true,
        ]);

        Event::create([
            'title' => 'Kapakyanan Festival',
            'slug' => 'kapakyanan-festival',
            'starts_at' => '2026-10-15 00:00:00',
            'location' => 'Victoria, Oriental Mindoro (venue to be confirmed)',
            'description' => 'The programme and venue are not yet confirmed.',
            'is_published' => true,
        ]);

        $view = app(EventController::class)->index();
        $events = $view->getData()['events'];

        $this->assertSame('public.events.index', $view->name());
        $this->assertSame(
            ['Kapakyanan Festival', 'Past town activity'],
            $events->getCollection()->pluck('title')->all(),
        );
        $this->assertSame('2026-10-15 00:00:00', $events->first()->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('Victoria, Oriental Mindoro (venue to be confirmed)', $events->first()->location);
    }
}
