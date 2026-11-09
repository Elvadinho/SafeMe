<?php

namespace Modules\Documents\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

/** Follow your post without an account: the post id (URL) + the phone you used. */
class PhoneCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone')]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'regex:' . Phone::PATTERN],
        ];
    }
}
