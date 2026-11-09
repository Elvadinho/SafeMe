<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route middleware already checks role:super_admin
    }

    public function rules(): array
    {
        return [
            // Only admin <-> citizen. Nobody can create another super admin through the API.
            'role' => ['required', Rule::in([UserRole::Admin->value, UserRole::Citizen->value])],
        ];
    }
}
