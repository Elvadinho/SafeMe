<?php

namespace Modules\Documents\Enums;

enum FoundStatus: string
{
    case Available = 'available'; // waiting for its owner
    case Matched   = 'matched';   // an admin confirmed the owner: contacts are shared
    case Returned  = 'returned';  // given back
}
