<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('category')?->id;
        return [
            'name'        => ['required','string','max:80'],
            'slug'        => ['nullable','string','max:90', Rule::unique('categories','slug')->ignore($id)],
            'icon'        => ['nullable','string','max:8'],
            'color'       => ['required','string','regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable','string','max:255'],
            'sort_order'  => ['integer','min:0','max:65535'],
            'is_active'   => ['boolean'],
        ];
    }
}
