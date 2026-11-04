<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeArticleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'title'        => ['required','string','max:200'],
            'category'     => ['required','string','max:60'],
            'content'      => ['required','string','min:20'],
            'source'       => ['nullable','string','max:180'],
            'tags'         => ['nullable','array','max:12'],
            'tags.*'       => ['string','max:40'],
            'is_published' => ['boolean'],
        ];
    }
}
