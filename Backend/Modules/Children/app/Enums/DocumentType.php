<?php

namespace Modules\Children\Enums;

enum DocumentType: string
{
  case BirthCertificate  = 'birth_certificate';  // acte de naissance / jugement supplétif
  case ReporterId        = 'reporter_id';        // CNI, passport or CNI receipt
  case GuardianshipOrder = 'guardianship_order'; // jugement de tutelle / délégation de garde
  case PhotoWithChild    = 'photo_with_child';   // supporting evidence only
}
