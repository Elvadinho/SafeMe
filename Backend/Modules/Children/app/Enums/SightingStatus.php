<?php

namespace Modules\Children\Enums;

enum SightingStatus: string
{
  case Pending = 'pending';
  case Confirmed = 'confirmed';
  case Dismissed = 'dismissed';
}
