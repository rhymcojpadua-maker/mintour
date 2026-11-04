<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Favorite;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $q = Destination::published()->with('category');

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%$search%")
                    ->orWhere('short_description', 'like', "%$search%")
                    ->orWhere('long_description', 'like', "%$search%");
            });
        }

        if ($cat = $request->string('cat')->toString()) {
            $q->whereHas('category', fn ($c) => $c->where('slug', $cat));
        }

        $sort = $request->string('sort', 'rating')->toString();
        match ($sort) {
            'name'     => $q->orderBy('name'),
            'distance' => $q->orderBy('distance_km'),
            default    => $q->orderByDesc('rating_avg')->orderByDesc('rating_count'),
        };

        return view('public.destinations.index', [
            'destinations' => $q->paginate(12)->withQueryString(),
            'categories'   => Category::active()->ordered()->get(),
            'filters'      => ['q' => $search ?? '', 'cat' => $cat ?? '', 'sort' => $sort],
        ]);
    }

    public function show(Destination $destination)
    {
        $destination->load(['category', 'photos', 'reviews.user']);

        $isFavorited = auth()->check()
            && Favorite::where('user_id', auth()->id())
                ->where('destination_id', $destination->id)->exists();

        return view('public.destinations.show', [
            'destination' => $destination,
            'nearby'      => $destination->nearby(4),
            'isFavorited' => $isFavorited,
        ]);
    }

    public function storeReview(StoreReviewRequest $request, Destination $destination)
    {
        $review = $destination->reviews()->create([
            'user_id'     => auth()->id(),
            'rating'      => $request->integer('rating'),
            'body'        => $request->string('body'),
            'is_approved' => true,
        ]);

        $destination->recalcRating();

        return back()->with('status', 'Review posted — thanks!');
    }
}
