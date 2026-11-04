<?php

namespace App\Http\Controllers\Admin;

class FaqController extends CrudController
{
    protected string $modelClass = 'App\Models\Faq';

    protected string $routeParameter = 'faq';

    protected string $resourceName = 'faqs';

    protected string $title = 'FAQ';

    protected array $validation = ['question' => ['required', 'string', 'max:255'], 'answer' => ['required', 'string'], 'category' => ['nullable', 'string', 'max:60'], 'sort_order' => ['nullable', 'integer'], 'is_published' => ['sometimes', 'boolean']];
}
