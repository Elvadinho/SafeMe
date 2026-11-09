<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** The super admin creates a moderator / police account. */
class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route middleware already checks role:super_admin
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone')]);
        }
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:120'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'regex:' . Phone::PATTERN, 'unique:users,phone'],
            'password' => ['required', Password::min(12)->letters()->numbers()],
        ];
    }
}
