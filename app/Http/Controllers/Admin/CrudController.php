<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class CrudController extends Controller
{
    protected string $modelClass;

    protected string $routeParameter;

    protected string $resourceName;

    protected string $title;

    protected array $validation = [];

    protected array $with = [];

    public function index(Request $request)
    {
        if ($this->resourceName === 'users') {
            $this->authorize('viewAny', $this->modelClass);
        }

        $search = $request->string('q')->trim()->toString();
        $query = ($this->modelClass)::query()->with($this->with)->latest();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                foreach ($this->searchColumns() as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return view('admin.generic.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'title' => $this->title,
            'resource' => $this->resourceName,
            'routeParameter' => $this->routeParameter,
            'search' => $search,
        ]);
    }

    public function create(Request $request)
    {
        if ($this->resourceName === 'users') {
            $this->authorize('create', $this->modelClass);
        }

        return view('admin.generic.form', [
            'item' => null,
            'title' => 'Create '.$this->title,
            'resource' => $this->resourceName,
            'routeParameter' => $this->routeParameter,
            'fields' => $this->formFields(),
            'prefill' => $request->only(['title', 'question', 'content']),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->resourceName === 'users') {
            $this->authorize('create', $this->modelClass);
        }

        $rules = $this->validation;
        if ($this->resourceName === 'users' && isset($rules['password'])) {
            $rules['password'][0] = 'required';
        }

        $data = $request->validate($rules);
        $data = $this->normalize($data, $request);
        ($this->modelClass)::create($data);

        return redirect()->route('admin.'.$this->resourceName.'.index')
            ->with('status', $this->title.' created.');
    }

    public function edit(Request $request)
    {
        $item = $this->resolve($request);
        if ($this->resourceName === 'users') {
            $this->authorize('update', $item);
        }

        return view('admin.generic.form', [
            'item' => $item,
            'title' => 'Edit '.$this->title,
            'resource' => $this->resourceName,
            'routeParameter' => $this->routeParameter,
            'fields' => $this->formFields(),
            'prefill' => [],
        ]);
    }

    public function update(Request $request)
    {
        $item = $this->resolve($request);
        if ($this->resourceName === 'users') {
            $this->authorize('update', $item);
        }

        $data = $request->validate($this->validation);
        $data = $this->normalize($data, $request, $item);
        $item->update($data);

        return redirect()->route('admin.'.$this->resourceName.'.index')
            ->with('status', $this->title.' updated.');
    }

    public function destroy(Request $request)
    {
        $item = $this->resolve($request);
        if ($this->resourceName === 'users') {
            $this->authorize('delete', $item);
        }

        $item->delete();

        return back()->with('status', $this->title.' deleted.');
    }

    protected function resolve(Request $request): Model
    {
        $value = $request->route($this->routeParameter);
        if ($value instanceof Model) {
            return $value;
        }

        $model = new ($this->modelClass)();
        $key = $model->getRouteKeyName();

        return $key === $model->getKeyName()
            ? $model->newQuery()->findOrFail($value)
            : $model->newQuery()->where($key, $value)->firstOrFail();
    }

    protected function normalize(array $data, Request $request, ?Model $item = null): array
    {
        $model = new ($this->modelClass)();
        if ($model->isFillable('slug')) {
            $source = ($data['slug'] ?? '') ?: ($data['name'] ?? $data['title'] ?? '');
            $base = Str::slug($source);
            $slug = $base;
            $suffix = 1;

            while (($this->modelClass)::query()
                ->where('slug', $slug)
                ->when($item, fn ($query) => $query->whereKeyNot($item->getKey()))
                ->exists()) {
                $slug = $base.'-'.++$suffix;
            }

            $data['slug'] = $slug;
        }

        if ($request->hasFile('cover_image')) {
            if ($item && $item->cover_image && ! Str::startsWith($item->cover_image, ['http://', 'https://'])) {
                Storage::disk('public')->delete($item->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')->store('uploads', 'public');
        }

        if (isset($data['amenities']) && is_string($data['amenities'])) {
            $data['amenities'] = array_values(array_filter(array_map('trim', explode(',', $data['amenities']))));
        }

        if (isset($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
        }

        if ($this->resourceName === 'users' && empty($data['password'])) {
            unset($data['password']);
        }

        if ($this->resourceName === 'knowledge') {
            $data[$item ? 'updated_by' : 'created_by'] = $request->user()->id;
            if ($item) {
                $data['updated_by'] = $request->user()->id;
            }
        }

        return $data;
    }

    protected function searchColumns(): array
    {
        return match ($this->resourceName) {
            'accommodations', 'restaurants', 'products' => ['name', 'address'],
            'categories' => ['name', 'slug'],
            'events' => ['title', 'location'],
            'announcements' => ['title', 'body'],
            'emergency-contacts' => ['name', 'phone', 'category'],
            'faqs' => ['question', 'answer', 'category'],
            'knowledge' => ['title', 'content', 'category'],
            'users' => ['name', 'email'],
            default => ['name'],
        };
    }

    protected function formFields(): array
    {
        $text = fn (string $name, string $label, bool $required = false, string $hint = '') => compact('name', 'label', 'required', 'hint') + ['type' => 'text'];
        $area = fn (string $name, string $label, bool $required = false, string $hint = '') => compact('name', 'label', 'required', 'hint') + ['type' => 'textarea'];
        $number = fn (string $name, string $label, bool $required = false, string $step = '1') => compact('name', 'label', 'required', 'step') + ['type' => 'number'];
        $check = fn (string $name, string $label, bool $default = true) => compact('name', 'label', 'default') + ['type' => 'checkbox'];
        $select = fn (string $name, string $label, array $options, bool $required = false) => compact('name', 'label', 'options', 'required') + ['type' => 'select'];

        return match ($this->resourceName) {
            'announcements' => [
                $text('title', 'Title', true),
                $area('body', 'Announcement', true),
                $select('priority', 'Priority', ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'], true),
                ['name' => 'published_at', 'label' => 'Publish date', 'type' => 'datetime-local'],
                $check('is_active', 'Active'),
            ],
            'categories' => [
                $text('name', 'Name', true),
                $text('slug', 'Slug', false, 'Leave blank to generate it from the name.'),
                $text('icon', 'Icon or emoji'),
                ['name' => 'color', 'label' => 'Category color', 'type' => 'color', 'default' => '#0f766e', 'required' => true],
                $text('description', 'Description'),
                $number('sort_order', 'Sort order'),
                $check('is_active', 'Active'),
            ],
            'accommodations' => [
                $text('name', 'Name', true),
                $text('slug', 'Slug'),
                $text('type', 'Type', true),
                $number('price_per_night', 'Price per night (PHP)', false, '0.01'),
                $number('rating_avg', 'Rating', false, '0.01'),
                $text('address', 'Address'),
                $number('latitude', 'Latitude', false, 'any'),
                $number('longitude', 'Longitude', false, 'any'),
                $text('contact', 'Contact details'),
                $text('amenities', 'Amenities', false, 'Separate amenities with commas.'),
                $area('description', 'Description', true),
                ['name' => 'cover_image', 'label' => 'Cover image', 'type' => 'image'],
                $check('is_published', 'Published'),
            ],
            'restaurants' => [
                $text('name', 'Name', true),
                $text('slug', 'Slug'),
                $text('cuisine', 'Cuisine or venue type', true),
                $text('price_range', 'Price range'),
                $number('rating_avg', 'Rating', false, '0.01'),
                $text('address', 'Address'),
                $number('latitude', 'Latitude', false, 'any'),
                $number('longitude', 'Longitude', false, 'any'),
                $text('hours', 'Opening hours'),
                $area('description', 'Description', true),
                ['name' => 'cover_image', 'label' => 'Cover image', 'type' => 'image'],
                $check('is_published', 'Published'),
            ],
            'products' => [
                $text('name', 'Name', true),
                $text('slug', 'Slug'),
                $text('maker', 'Maker', true),
                $number('price', 'Price (PHP)', true, '0.01'),
                $area('description', 'Description', true),
                ['name' => 'cover_image', 'label' => 'Product image', 'type' => 'image'],
                $check('is_published', 'Published'),
            ],
            'events' => [
                $text('title', 'Title', true),
                $text('slug', 'Slug'),
                ['name' => 'starts_at', 'label' => 'Starts at', 'type' => 'datetime-local', 'required' => true],
                ['name' => 'ends_at', 'label' => 'Ends at', 'type' => 'datetime-local'],
                $text('location', 'Location', true),
                $text('category', 'Category'),
                $area('description', 'Description', true),
                ['name' => 'cover_image', 'label' => 'Cover image', 'type' => 'image'],
                $check('is_published', 'Published'),
            ],
            'emergency-contacts' => [
                $text('name', 'Agency or contact name', true),
                $text('phone', 'Phone number', true),
                $text('category', 'Category'),
                $number('sort_order', 'Sort order'),
            ],
            'faqs' => [
                $text('question', 'Question', true),
                $area('answer', 'Answer', true),
                $text('category', 'Category'),
                $number('sort_order', 'Sort order'),
                $check('is_published', 'Published'),
            ],
            'knowledge' => [
                $text('title', 'Article title', true),
                $text('category', 'Category', true),
                $text('source', 'Source or verification'),
                $area('content', 'Knowledge article', true, 'Use accurate, verifiable details for chatbot answers.'),
                $text('tags', 'Tags', false, 'Separate tags with commas.'),
                $check('is_published', 'Published', false),
            ],
            'users' => [
                $text('name', 'Full name', true),
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                $select('role', 'Role', [
                    'user' => 'Visitor',
                    'staff' => 'Staff',
                    'admin' => 'Administrator',
                    'super_admin' => 'Super Admin',
                ], true),
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'hint' => 'At least 8 characters; leave blank when editing to keep the current password.'],
                ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password'],
                $select('preferred_lang', 'Preferred language', ['en' => 'English', 'fil' => 'Filipino']),
                $check('is_active', 'Active'),
            ],
            default => [],
        };
    }
}
