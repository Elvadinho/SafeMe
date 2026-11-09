<?php

namespace Modules\Documents\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Documents\Enums\MatchStatus;

/** Created by the app only (never from a request), so nothing is fillable from outside. */
class DocumentMatch extends Model
{
    protected $fillable = ['found_document_id', 'lost_declaration_id'];

    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'status'      => MatchStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function foundDocument(): BelongsTo
    {
        return $this->belongsTo(FoundDocument::class);
    }

    public function lostDeclaration(): BelongsTo
    {
        return $this->belongsTo(LostDeclaration::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
