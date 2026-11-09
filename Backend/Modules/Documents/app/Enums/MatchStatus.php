<?php

namespace Modules\Documents\Enums;

/** A possible "this found document belongs to this person", waiting for an admin. */
enum MatchStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed'; // contacts are revealed to both sides
    case Rejected  = 'rejected';
}
