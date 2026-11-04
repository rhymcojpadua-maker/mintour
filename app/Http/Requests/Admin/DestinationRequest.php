<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Destination::class)
            || $this->user()?->can('update', $this->route('destination') ?? new \App\Models\Destination);
    }

    public function rules(): array
    {
        $id = $this->route('destination')?->id;

        return [
            'name'              => ['required', 'string', 'max:150'],
            'slug'              => ['nullable', 'string', 'max:160', Rule::unique('destinations','slug')->ignore($id)],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'tagline'           => ['nullable', 'string', 'max:120'],
            'short_description' => ['required', 'string', 'max:400'],
            'long_description'  => ['required', 'string', 'min:20'],
            'latitude'          => ['required', 'numeric', 'between:-90,90'],
            'longitude'         => ['required', 'numeric', 'between:-180,180'],
            'address'           => ['nullable', 'string', 'max:255'],
            'hours'             => ['nullable', 'string', 'max:120'],
            'entrance_fee'      => ['nullable', 'string', 'max:120'],
            'best_time'         => ['nullable', 'string', 'max:120'],
            'distance_km'       => ['nullable', 'numeric', 'min:0', 'max:999'],
            'cover_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured'       => ['boolean'],
            'is_published'      => ['boolean'],
            'sort_order'        => ['integer', 'min:0', 'max:65535'],
            'photos'            => ['nullable', 'array', 'max:12'],
            'photos.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
