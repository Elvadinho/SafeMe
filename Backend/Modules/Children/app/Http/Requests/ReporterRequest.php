<?php

namespace Modules\Children\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Modules\Children\Enums\DocumentType;

/**
 * The reporter comes back to follow the case or add proof documents.
 * No account: they prove who they are with the report id (in the URL)
 * + the phone number they used. Both must match.
 */
class ReporterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reporter_phone' => Phone::normalize($this->input('reporter_phone')) ?? $this->input('reporter_phone')]);
    }

    public function rules(): array
    {
        $uploads = config('safeme.uploads');

        return [
            'reporter_phone'   => ['required', 'regex:' . Phone::PATTERN],

            // Only used by the "add documents" endpoint
            'documents'        => [Rule::requiredIf($this->routeIs('*.documents.store')), 'array', 'max:' . $uploads['max_documents']],
            'documents.*.type' => ['required', 'distinct', Rule::enum(DocumentType::class)],
            'documents.*.file' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp'])->max($uploads['max_document_kb'])],
        ];
    }
}
