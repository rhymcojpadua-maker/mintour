<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Event;
use App\Models\Faq;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featured = Destination::published()->featured()->with('category')->take(3)->get();

        return view('public.home', [
            'featured'      => $featured,
            'mapMarkers'    => $featured->map(fn ($destination) => [
                'name'   => $destination->name,
                'lat'    => $destination->latitude,
                'lng'    => $destination->longitude,
                'color'  => $destination->category->color,
                'url'    => route('destinations.show', $destination),
                'rating' => $destination->rating_avg,
            ])->values(),
            'categories'    => Category::active()->ordered()->withCount('destinations')->get(),
            'upcoming'      => Event::published()->upcoming()->take(3)->get(),
            'stats'         => [
                'destinations' => Destination::published()->count(),
                'businesses'   => \App\Models\Accommodation::published()->count()
                                + \App\Models\Restaurant::published()->count(),
                'events'       => Event::published()->count(),
            ],
        ]);
    }
}
