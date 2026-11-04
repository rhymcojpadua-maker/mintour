<?php
namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::published()
            ->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END', [now()])
            ->orderBy('starts_at')
            ->paginate(12);

        return view('public.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_unless($event->is_published, 404);

        return view('public.events.show', compact('event'));
    }
}
