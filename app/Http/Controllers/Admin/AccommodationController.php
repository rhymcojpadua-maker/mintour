<?php

namespace App\Http\Controllers\Admin;

class AccommodationController extends CrudController
{
    protected string $modelClass = 'App\Models\Accommodation';

    protected string $routeParameter = 'accommodation';

    protected string $resourceName = 'accommodations';

    protected string $title = 'Accommodation';

    protected array $validation = ['name' => ['required', 'string', 'max:150'], 'slug' => ['nullable', 'string', 'max:160'], 'type' => ['required', 'string', 'max:60'], 'price_per_night' => ['nullable', 'numeric', 'min:0'], 'rating_avg' => ['nullable', 'numeric', 'min:0', 'max:5'], 'address' => ['nullable', 'string', 'max:255'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'contact' => ['nullable', 'string', 'max:255'], 'amenities' => ['nullable', 'string'], 'description' => ['required', 'string'], 'cover_image' => ['nullable', 'image', 'max:4096'], 'is_published' => ['sometimes', 'boolean']];
}
