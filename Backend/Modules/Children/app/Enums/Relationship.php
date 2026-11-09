<?php

namespace Modules\Children\Enums;

use Modules\Children\Enums\DocumentType;

enum Relationship: string
{
  case Parent   = 'parent';
  case Guardian = 'guardian'; // legal tutor, needs a court decision (jugement de tutelle)

  /** Documents the admin expects to see for this relationship. */
  public function requiredDocuments(): array
  {
    return match ($this) {
      self::Parent   => [DocumentType::BirthCertificate, DocumentType::ReporterId],
      self::Guardian => [DocumentType::GuardianshipOrder, DocumentType::ReporterId],
    };
  }
}
