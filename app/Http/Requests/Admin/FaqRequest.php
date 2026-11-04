<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'question'     => ['required','string','max:255'],
            'answer'       => ['required','string'],
            'category'     => ['nullable','string','max:60'],
            'sort_order'   => ['integer','min:0','max:65535'],
            'is_published' => ['boolean'],
        ];
    }
}
