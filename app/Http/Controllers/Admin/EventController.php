<?php

namespace App\Http\Controllers\Admin;

class EventController extends CrudController
{
    protected string $modelClass = 'App\Models\Event';

    protected string $routeParameter = 'event';

    protected string $resourceName = 'events';

    protected string $title = 'Event';

    protected array $validation = ['title' => ['required', 'string', 'max:180'], 'slug' => ['nullable', 'string', 'max:190'], 'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date'], 'location' => ['required', 'string', 'max:180'], 'category' => ['nullable', 'string', 'max:60'], 'description' => ['required', 'string'], 'cover_image' => ['nullable', 'image', 'max:4096'], 'is_published' => ['sometimes', 'boolean']];
}
