<?php

namespace App\Http\Controllers\Admin;

class AnnouncementController extends CrudController
{
    protected string $modelClass = 'App\Models\Announcement';

    protected string $routeParameter = 'announcement';

    protected string $resourceName = 'announcements';

    protected string $title = 'Announcement';

    protected array $validation = ['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string'], 'priority' => ['required', 'in:low,normal,high'], 'is_active' => ['sometimes', 'boolean'], 'published_at' => ['nullable', 'date']];
}
