<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // only super admins manage users
        return $this->user()?->role->value === 'super_admin';
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name'     => ['required','string','max:120'],
            'email'    => ['required','email','max:190', Rule::unique('users','email')->ignore($id)],
            'role'     => ['required','in:super_admin,admin,staff,user'],
            'password' => [$id ? 'nullable' : 'required','confirmed', Password::min(8)->letters()->numbers()],
            'is_active'=> ['boolean'],
        ];
    }
}
