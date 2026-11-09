<?php

namespace Modules\Children\Http\Requests;

use App\Support\Geo;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/** "I saw this child". Anyone can send it, logged in or not. */
class StoreSightingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('witness_phone')) {
            $this->merge(['witness_phone' => Phone::normalize($this->input('witness_phone')) ?? $this->input('witness_phone')]);
        }
    }

    public function rules(): array
    {
        return [
            'lat'           => Geo::latRules(),
            'lng'           => Geo::lngRules(),
            'seen_at'       => ['required', 'date', 'before_or_equal:now'],
            'note'          => ['nullable', 'string', 'max:1000'],
            'photo'         => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(config('safeme.uploads.max_photo_kb'))],
            // Optional: lets the police call back the witness. Never shown publicly.
            'witness_phone' => ['nullable', 'regex:' . Phone::PATTERN],
        ];
    }
}
