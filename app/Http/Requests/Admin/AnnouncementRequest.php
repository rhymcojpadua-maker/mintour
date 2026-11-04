<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'title'        => ['required','string','max:180'],
            'body'         => ['required','string'],
            'priority'     => ['required','in:low,normal,high'],
            'is_active'    => ['boolean'],
            'published_at' => ['nullable','date'],
        ];
    }
}
