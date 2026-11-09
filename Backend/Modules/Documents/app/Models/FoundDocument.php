<?php

namespace Modules\Documents\Models;

use App\Support\Text;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Documents\Enums\DocumentKind;
use Modules\Documents\Enums\FoundStatus;

#[Fillable([
    'doc_type', 'owner_name', 'number_last_digits', 'found_city', 'found_place', 'found_at',
    'finder_user_id', 'finder_name', 'finder_phone',
])]
#[Hidden(['id', 'owner_name_key', 'finder_user_id', 'finder_name', 'finder_phone', 'number_last_digits'])]
class FoundDocument extends Model
{
    use HasUuids;

    protected $attributes = ['status' => 'available'];

    protected function casts(): array
    {
        return [
            'doc_type' => DocumentKind::class,
            'status'   => FoundStatus::class,
            'found_at' => 'date',
        ];
    }

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** The matching key is always computed from the name, never sent by the client. */
    protected static function booted(): void
    {
        static::saving(fn (FoundDocument $doc) => $doc->owner_name_key = Text::nameKey($doc->owner_name));
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DocumentMatch::class);
    }

    /** What anyone may see: enough to recognise your document, not enough to steal an identity. */
    public function toPublicArray(): array
    {
        return [
            'public_id'  => $this->public_id,
            'doc_type'   => $this->doc_type,
            'owner_name' => Text::maskName($this->owner_name),
            'found_city' => $this->found_city,
            'found_at'   => $this->found_at->toDateString(),
            'status'     => $this->status,
        ];
    }
}
