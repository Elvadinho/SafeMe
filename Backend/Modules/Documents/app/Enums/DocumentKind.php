<?php

namespace Modules\Documents\Enums;

enum DocumentKind: string
{
    case NationalId       = 'national_id';      // CNI
    case Passport         = 'passport';
    case BirthCertificate = 'birth_certificate';
    case DrivingLicence   = 'driving_licence';
    case Diploma          = 'diploma';
    case StudentCard      = 'student_card';
    case Other            = 'other';
}
