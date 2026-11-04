<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    public function index()
    {
        $categories = Category::active()->ordered()->get();
        $activeFilters = array_merge($categories->pluck('slug')->all(), ['accommodation', 'restaurant']);

        return view('public.map', [
            'categories' => $categories,
            'activeFilters' => $activeFilters,
            'initial' => $this->mapFeatures($this->selectedFeatureTypes($activeFilters)),
        ]);
    }

    public function features(Collection $rows): array
    {
        return $rows->map(fn ($d) => [
            'id'       => $d->id,
            'name'     => $d->name,
            'slug'     => $d->slug,
            'category' => $d->category->name,
            'type'     => 'destination',
            'color'    => $d->category->color,
            'lat'      => $d->latitude,
            'lng'      => $d->longitude,
            'rating'   => $d->rating_avg,
            'distance' => $d->distance_km,
            'address'  => $d->address,
            'details_url' => route('destinations.show', $d),
            'popup'    => $this->popup($d->name, $d->category->name, $d->address, route('destinations.show', $d), $d->latitude, $d->longitude),
        ])->all();
    }

    /** API endpoint consumed by the map to apply filters server-side. */
    public function data(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string', 'max:50'],
        ]);

        return response()->json([
            'features' => $this->mapFeatures($this->selectedFeatureTypes($filters['categories'] ?? null)),
        ]);
    }

    private function selectedFeatureTypes(?array $selected): array
    {
        $destinations = Destination::published()
            ->whereHas('category', fn ($category) => $category->active())
            ->with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($selected !== null) {
            $destinations->whereHas('category', fn ($category) => $category->whereIn('slug', $selected));
        }

        $types = $selected === null
            ? ['accommodation', 'restaurant']
            : array_values(array_intersect($selected, ['accommodation', 'restaurant']));

        return [
            'destinations' => $destinations->get(),
            'accommodations' => in_array('accommodation', $types, true)
                ? Accommodation::published()->whereNotNull('latitude')->whereNotNull('longitude')->get()
                : collect(),
            'restaurants' => in_array('restaurant', $types, true)
                ? Restaurant::published()->whereNotNull('latitude')->whereNotNull('longitude')->get()
                : collect(),
        ];
    }

    private function mapFeatures(array $rows): array
    {
        $features = $this->features($rows['destinations']);

        foreach ($rows['accommodations'] as $accommodation) {
            $features[] = [
                'id' => $accommodation->id,
                'name' => $accommodation->name,
                'slug' => $accommodation->slug,
                'category' => $accommodation->type,
                'type' => 'accommodation',
                'color' => '#d97706',
                'lat' => $accommodation->latitude,
                'lng' => $accommodation->longitude,
                'address' => $accommodation->address,
                'details_url' => route('accommodations.show', $accommodation),
                'popup' => $this->popup(
                    $accommodation->name,
                    $accommodation->type,
                    $accommodation->address,
                    route('accommodations.show', $accommodation),
                    $accommodation->latitude,
                    $accommodation->longitude,
                ),
            ];
        }

        foreach ($rows['restaurants'] as $restaurant) {
            $features[] = [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'category' => $restaurant->cuisine,
                'type' => 'restaurant',
                'color' => '#e11d48',
                'lat' => $restaurant->latitude,
                'lng' => $restaurant->longitude,
                'address' => $restaurant->address,
                'details_url' => route('restaurants.show', $restaurant),
                'popup' => $this->popup(
                    $restaurant->name,
                    $restaurant->cuisine,
                    $restaurant->address,
                    route('restaurants.show', $restaurant),
                    $restaurant->latitude,
                    $restaurant->longitude,
                ),
            ];
        }

        return $features;
    }

    private function popup(string $name, string $category, ?string $address, string $detailsUrl, float|string $latitude, float|string $longitude): string
    {
        return view('public.map._popup', [
            'name' => $name,
            'category' => $category,
            'address' => $address,
            'detailsUrl' => $detailsUrl,
            'mapUrl' => 'https://www.google.com/maps/dir/?api=1&destination='.$latitude.','.$longitude,
            'outsideVictoria' => $address && ! str_contains(strtolower($address), 'victoria'),
        ])->render();
    }
}
