<?php

namespace App\Enums;

/**
 * Who a logged-in user is. Stored as a string in users.role.
 * Reporters (parents/guardians) are NOT users: they are attached to a report
 * by name + phone, so a panicked parent never needs an account.
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin'; // the owner: manages everything, creates admins
    case Admin      = 'admin';       // moderator / police: verifies reports and documents
    case Citizen    = 'citizen';     // public: sightings, found documents, notifications

    /** Can this role approve, reject or block alerts? */
    public function canModerate(): bool
    {
        return match ($this) {
            self::SuperAdmin, self::Admin => true,
            self::Citizen                => false,
        };
    }

    /** Translatable label key for the frontend, e.g. "roles.super_admin". */
    public function labelKey(): string
    {
        return 'roles.' . $this->value;
    }
}