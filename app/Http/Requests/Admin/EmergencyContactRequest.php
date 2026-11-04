<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyContactRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'name'       => ['required','string','max:120'],
            'phone'      => ['required','string','max:60'],
            'category'   => ['nullable','string','max:60'],
            'sort_order' => ['integer','min:0','max:65535'],
        ];
    }
}
