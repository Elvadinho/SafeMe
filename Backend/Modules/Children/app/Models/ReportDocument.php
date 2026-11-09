<?php

namespace Modules\Children\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Children\Enums\DocumentType;

#[Fillable(['type', 'path', 'mime_type', 'size_bytes'])]
#[Hidden(['path'])] // the file is only served through an admin-protected route
class ReportDocument extends Model
{
  protected function casts(): array
  {
    return ['type' => DocumentType::class, 'size_bytes' => 'integer'];
  }

  public function report(): BelongsTo
  {
    return $this->belongsTo(MissingReport::class, 'missing_report_id');
  }
}
