<?php

namespace Modules\Children\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Children\Enums\DocumentType;
use Modules\Children\Enums\Relationship;
use Modules\Children\Enums\ReportStatus;

// SECURITY: status / reviewed_by / reviewed_at are NOT fillable:
// a reporter must never be able to send "status":"approved".
#[Fillable([
  'child_name',
  'child_age',
  'child_gender',
  'child_photo_path',
  'description',
  'last_seen_at',
  'last_seen_lat',
  'last_seen_lng',
  'last_seen_place',
  'reporter_name',
  'reporter_phone',
  'relationship',
])]
// Never sent in JSON by default. Admin screens call makeVisible() when they need them.
#[Hidden(['id', 'reporter_name', 'reporter_phone', 'reviewed_by', 'child_photo_path', 'rejection_reason', 'deleted_at'])]
class MissingReport extends Model
{
  use HasUuids, SoftDeletes;

  /** Same default as the DB, so a freshly created object is already "pending" in PHP. */
  protected $attributes = [
    'status' => 'pending',
  ];

  /** Extra fields added to the JSON. */
  protected $appends = ['photo_url'];

  protected function casts(): array
  {
    return [
      'status'        => ReportStatus::class,
      'relationship'  => Relationship::class,
      'last_seen_at'  => 'datetime',
      'reviewed_at'   => 'datetime',
      'resolved_at'   => 'datetime',
      'last_seen_lat' => 'decimal:7',
      'last_seen_lng' => 'decimal:7',
      'child_age'     => 'integer',
    ];
  }

  /** HasUuids fills public_id automatically; `id` stays the fast internal key. */
  public function uniqueIds(): array
  {
    return ['public_id'];
  }

  /** URLs use public_id: /api/v1/alerts/9f1c2b... */
  public function getRouteKeyName(): string
  {
    return 'public_id';
  }

  // ── Relations ──────────────────────────────────────
  public function documents(): HasMany
  {
    return $this->hasMany(ReportDocument::class);
  }

  public function sightings(): HasMany
  {
    return $this->hasMany(Sighting::class);
  }

  public function reviewer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'reviewed_by');
  }

  // ── Query scopes ────────────────────────────────────
  // Usage: MissingReport::visibleToPublic()->get()
  #[Scope]
  protected function visibleToPublic(Builder $query): void
  {
    $query->where('status', ReportStatus::Approved);
  }

  #[Scope]
  protected function awaitingReview(Builder $query): void
  {
    $query->where('status', ReportStatus::Pending)->oldest(); // oldest first = fair queue
  }

  // ── Helpers ─────────────────────────────────────────
  /** URL of the child's photo (the controller checks who may see it). */
  protected function photoUrl(): Attribute
  {
    return Attribute::get(fn () => $this->public_id ? route('api.alerts.photo', $this) : null);
  }

  /**
   * Which proof documents are still missing?
   * Parent → birth certificate + ID card. Guardian → court decision + ID card.
   *
   * @return list<DocumentType>
   */
  public function missingDocumentTypes(): array
  {
    $required = $this->relationship->requiredDocuments();
    $uploaded = $this->documents->pluck('type')->all();

    return array_values(array_filter(
      $required,
      fn (DocumentType $type) => ! in_array($type, $uploaded, true),
    ));
  }
}
