<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('event')?->id;
        return [
            'title'        => ['required','string','max:180'],
            'slug'         => ['nullable','string','max:190', Rule::unique('events','slug')->ignore($id)],
            'starts_at'    => ['required','date'],
            'ends_at'      => ['nullable','date','after_or_equal:starts_at'],
            'location'     => ['required','string','max:180'],
            'category'     => ['nullable','string','max:60'],
            'description'  => ['required','string'],
            'cover_image'  => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_published' => ['boolean'],
        ];
    }
}
