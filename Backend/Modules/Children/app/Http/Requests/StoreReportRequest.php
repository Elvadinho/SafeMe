<?php

namespace Modules\Children\Http\Requests;

use App\Support\Geo;
use App\Support\Phone;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Modules\Children\Enums\DocumentType;
use Modules\Children\Enums\Relationship;

/**
 * A parent / guardian reports a missing child. No account needed.
 * Proof documents are optional here: the alert stays PENDING until an admin verifies.
 */
class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protection = rate limit + admin review
    }

    /** Runs BEFORE the rules: clean the input so the rules check the real value. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'reporter_phone' => Phone::normalize($this->input('reporter_phone')) ?? $this->input('reporter_phone'),
            'child_name'     => Text::clean($this->input('child_name')),
            'reporter_name'  => Text::clean($this->input('reporter_name')),
        ]);
    }

    public function rules(): array
    {
        $uploads = config('safeme.uploads');

        return [
            'child_name'      => ['required', 'string', 'min:2', 'max:120'],
            'child_age'       => ['required', 'integer', 'between:0,17'],
            'child_gender'    => ['nullable', Rule::in(['male', 'female'])],
            'child_photo'     => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max($uploads['max_photo_kb'])],
            'description'     => ['nullable', 'string', 'max:2000'],

            'last_seen_at'    => ['required', 'date', 'before_or_equal:now', 'after:' . now()->subDays(90)->toDateString()],
            'last_seen_lat'   => Geo::latRules(),
            'last_seen_lng'   => Geo::lngRules(),
            'last_seen_place' => ['nullable', 'string', 'max:160'],

            'reporter_name'   => ['required', 'string', 'min:2', 'max:120'],
            'reporter_phone'  => ['required', 'string', 'regex:' . Phone::PATTERN],
            'relationship'    => ['required', Rule::enum(Relationship::class)],

            'documents'        => ['nullable', 'array', 'max:' . $uploads['max_documents']],
            'documents.*.type' => ['required', 'distinct', Rule::enum(DocumentType::class)],
            'documents.*.file' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp'])->max($uploads['max_document_kb'])],

            // The reporter declares the information is true (a false report is an offence).
            'declaration'     => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'reporter_phone.regex'  => 'Enter a valid Cameroon number, e.g. 6 90 00 00 00.',
            'last_seen_lat.between' => 'The location must be inside Cameroon.',
            'last_seen_lng.between' => 'The location must be inside Cameroon.',
        ];
    }
}
