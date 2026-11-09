<?php

namespace Modules\Documents\Http\Requests;

use App\Support\Phone;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Documents\Enums\DocumentKind;

/** "I lost my document": you get notified when someone finds it. */
class StoreLostDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'owner_phone' => Phone::normalize($this->input('owner_phone')) ?? $this->input('owner_phone'),
            'owner_name'  => Text::clean($this->input('owner_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'doc_type'           => ['required', Rule::enum(DocumentKind::class)],
            // Write the name exactly as on the document (order and accents do not matter)
            'owner_name'         => ['required', 'string', 'min:3', 'max:120'],
            'number_last_digits' => ['nullable', 'digits_between:2,4'],
            'lost_city'          => ['nullable', 'string', 'max:80'],
            'owner_phone'        => ['required', 'regex:' . Phone::PATTERN],
        ];
    }
}
