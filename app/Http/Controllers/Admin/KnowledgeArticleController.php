<?php

namespace App\Http\Controllers\Admin;

class KnowledgeArticleController extends CrudController
{
    protected string $modelClass = 'App\Models\KnowledgeArticle';

    protected string $routeParameter = 'knowledge';

    protected string $resourceName = 'knowledge';

    protected string $title = 'Knowledge Article';

    protected array $validation = ['title' => ['required', 'string', 'max:200'], 'category' => ['required', 'string', 'max:60'], 'content' => ['required', 'string', 'min:20'], 'source' => ['nullable', 'string', 'max:180'], 'tags' => ['nullable', 'string'], 'is_published' => ['sometimes', 'boolean']];
}
