<?php

namespace Modules\Documents\Http\Requests;

use App\Support\Phone;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Documents\Enums\DocumentKind;

/** "I found a document". No account needed. */
class StoreFoundDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'finder_phone' => Phone::normalize($this->input('finder_phone')) ?? $this->input('finder_phone'),
            'owner_name'   => Text::clean($this->input('owner_name')),
            'finder_name'  => Text::clean($this->input('finder_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'doc_type'           => ['required', Rule::enum(DocumentKind::class)],
            'owner_name'         => ['required', 'string', 'min:3', 'max:120'],
            // Only the LAST digits: the full number is never stored (identity theft).
            'number_last_digits' => ['nullable', 'digits_between:2,4'],
            'found_city'         => ['required', 'string', 'max:80'],
            'found_place'        => ['nullable', 'string', 'max:160'],
            'found_at'           => ['required', 'date', 'before_or_equal:today'],
            'finder_name'        => ['required', 'string', 'min:2', 'max:120'],
            'finder_phone'       => ['required', 'regex:' . Phone::PATTERN],
        ];
    }
}
