<?php

namespace Modules\Documents\Models;

use App\Support\Text;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Documents\Enums\DocumentKind;
use Modules\Documents\Enums\LostStatus;

#[Fillable(['doc_type', 'owner_name', 'number_last_digits', 'lost_city', 'owner_phone'])]
#[Hidden(['id', 'owner_name_key', 'owner_phone', 'number_last_digits'])]
class LostDeclaration extends Model
{
    use HasUuids;

    protected $attributes = ['status' => 'open'];

    protected function casts(): array
    {
        return [
            'doc_type' => DocumentKind::class,
            'status'   => LostStatus::class,
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

    protected static function booted(): void
    {
        static::saving(fn (LostDeclaration $lost) => $lost->owner_name_key = Text::nameKey($lost->owner_name));
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DocumentMatch::class);
    }
}
