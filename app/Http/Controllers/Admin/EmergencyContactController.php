<?php

namespace App\Http\Controllers\Admin;

class EmergencyContactController extends CrudController
{
    protected string $modelClass = 'App\Models\EmergencyContact';

    protected string $routeParameter = 'emergency_contact';

    protected string $resourceName = 'emergency-contacts';

    protected string $title = 'Emergency Contact';

    protected array $validation = ['name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:60'], 'category' => ['nullable', 'string', 'max:60'], 'sort_order' => ['nullable', 'integer']];
}
