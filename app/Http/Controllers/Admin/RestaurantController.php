<?php

namespace App\Http\Controllers\Admin;

class RestaurantController extends CrudController
{
    protected string $modelClass = 'App\Models\Restaurant';

    protected string $routeParameter = 'restaurant';

    protected string $resourceName = 'restaurants';

    protected string $title = 'Restaurant';

    protected array $validation = ['name' => ['required', 'string', 'max:150'], 'slug' => ['nullable', 'string', 'max:160'], 'cuisine' => ['required', 'string', 'max:60'], 'price_range' => ['nullable', 'string', 'max:80'], 'rating_avg' => ['nullable', 'numeric', 'min:0', 'max:5'], 'address' => ['nullable', 'string', 'max:255'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'hours' => ['nullable', 'string', 'max:255'], 'description' => ['required', 'string'], 'cover_image' => ['nullable', 'image', 'max:4096'], 'is_published' => ['sometimes', 'boolean']];
}
