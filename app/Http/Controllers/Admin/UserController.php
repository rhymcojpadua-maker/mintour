<?php

namespace App\Http\Controllers\Admin;

class UserController extends CrudController
{
    protected string $modelClass = 'App\Models\User';

    protected string $routeParameter = 'user';

    protected string $resourceName = 'users';

    protected string $title = 'User';

    protected array $validation = ['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:190'], 'role' => ['required', 'in:super_admin,admin,staff,user'], 'password' => ['nullable', 'confirmed', 'min:8'], 'is_active' => ['sometimes', 'boolean']];
}
