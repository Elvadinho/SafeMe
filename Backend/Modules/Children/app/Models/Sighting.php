<?php

namespace Modules\Children\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Children\Enums\SightingStatus;

#[Fillable(['lat', 'lng', 'seen_at', 'note', 'photo_path', 'witness_phone', 'user_id'])]
#[Hidden(['witness_phone', 'user_id', 'photo_path', 'missing_report_id'])]
class Sighting extends Model
{
  /** Same default as the DB, so a new sighting is "pending" in PHP too. */
  protected $attributes = [
    'status' => 'pending',
  ];

  protected function casts(): array
  {
    return [
      'status'  => SightingStatus::class,
      'seen_at' => 'datetime',
      'lat'     => 'decimal:7',
      'lng'     => 'decimal:7',
    ];
  }

  public function report(): BelongsTo
  {
    return $this->belongsTo(MissingReport::class, 'missing_report_id');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
