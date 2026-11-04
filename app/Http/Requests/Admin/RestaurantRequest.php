<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('restaurant')?->id;
        return [
            'name'        => ['required','string','max:150'],
            'slug'        => ['nullable','string','max:160', Rule::unique('restaurants','slug')->ignore($id)],
            'cuisine'     => ['required','string','max:60'],
            'price_range' => ['nullable','string','max:40'],
            'address'     => ['nullable','string','max:255'],
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
            'hours'       => ['nullable','string','max:120'],
            'description' => ['required','string'],
            'cover_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_published'=> ['boolean'],
        ];
    }
}
