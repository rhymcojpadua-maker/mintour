<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccommodationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('accommodation')?->id;
        return [
            'name'            => ['required','string','max:150'],
            'slug'            => ['nullable','string','max:160', Rule::unique('accommodations','slug')->ignore($id)],
            'type'            => ['required','string','max:60'],
            'price_per_night' => ['nullable','numeric','min:0'],
            'address'         => ['nullable','string','max:255'],
            'latitude'        => ['nullable','numeric','between:-90,90'],
            'longitude'       => ['nullable','numeric','between:-180,180'],
            'contact'         => ['nullable','string','max:120'],
            'description'     => ['required','string'],
            'amenities'       => ['nullable','array'],
            'amenities.*'     => ['string','max:60'],
            'cover_image'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_published'    => ['boolean'],
        ];
    }
}
