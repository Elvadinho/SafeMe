<?php

namespace Modules\Children\Enums;

/**
 * Life of a report:
 *  pending ──approve──▶ approved ──▶ found
 *     │                    └──────▶ closed   (parent cancels, or the case ends)
 *     └──reject──▶ rejected (fake / unprovable)
 */
enum ReportStatus: string
{
  case Pending  = 'pending';
  case Approved = 'approved';
  case Rejected = 'rejected';
  case Found    = 'found';
  case Closed   = 'closed';

  /** Only approved alerts are shown to the public map. */
  public function isPublic(): bool
  {
    return $this === self::Approved;
  }

  /** Which statuses an admin may move this report to (state machine). */
  public function allowedTransitions(): array
  {
    return match ($this) {
      self::Pending  => [self::Approved, self::Rejected],
      self::Approved => [self::Found, self::Closed],
      self::Rejected, self::Found, self::Closed => [],
    };
  }

  public function canTransitionTo(self $next): bool
  {
    return in_array($next, $this->allowedTransitions(), true);
  }
}
