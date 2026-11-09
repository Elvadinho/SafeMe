<?php

namespace Modules\Documents\Enums;

enum LostStatus: string
{
    case Open    = 'open';    // still looking
    case Matched = 'matched'; // found and confirmed by an admin
    case Closed  = 'closed';
}
