<?php

namespace Modules\Children\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route middleware already checks role:admin,super_admin
    }

    public function rules(): array
    {
        return [
            // Always explain a rejection: it is kept for the police and shown to the reporter.
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
