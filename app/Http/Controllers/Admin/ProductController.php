<?php

namespace App\Http\Controllers\Admin;

class ProductController extends CrudController
{
    protected string $modelClass = 'App\Models\Product';

    protected string $routeParameter = 'product';

    protected string $resourceName = 'products';

    protected string $title = 'Product';

    protected array $validation = ['name' => ['required', 'string', 'max:150'], 'slug' => ['nullable', 'string', 'max:160'], 'maker' => ['required', 'string', 'max:150'], 'price' => ['required', 'numeric', 'min:0'], 'description' => ['required', 'string'], 'cover_image' => ['nullable', 'image', 'max:4096'], 'is_published' => ['sometimes', 'boolean']];
}
