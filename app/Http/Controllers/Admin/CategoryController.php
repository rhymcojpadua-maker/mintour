<?php

namespace App\Http\Controllers\Admin;

class CategoryController extends CrudController
{
    protected string $modelClass = 'App\Models\Category';

    protected string $routeParameter = 'category';

    protected string $resourceName = 'categories';

    protected string $title = 'Category';

    protected array $validation = ['name' => ['required', 'string', 'max:80'], 'slug' => ['nullable', 'string', 'max:90'], 'icon' => ['nullable', 'string', 'max:8'], 'color' => ['required', 'string'], 'description' => ['nullable', 'string', 'max:255'], 'sort_order' => ['nullable', 'integer'], 'is_active' => ['sometimes', 'boolean']];
}
