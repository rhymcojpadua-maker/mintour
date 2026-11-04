<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('product')?->id;
        return [
            'name'        => ['required','string','max:150'],
            'slug'        => ['nullable','string','max:160', Rule::unique('products','slug')->ignore($id)],
            'maker'       => ['required','string','max:150'],
            'price'       => ['required','numeric','min:0'],
            'description' => ['required','string'],
            'cover_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_published'=> ['boolean'],
        ];
    }
}
