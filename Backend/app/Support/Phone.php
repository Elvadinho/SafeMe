<?php

namespace App\Support;

/**
 * Cameroon phone numbers, always saved as +237 followed by 9 digits.
 * Same person = same string in the database, so limits and matching work.
 */
class Phone
{
    public const PATTERN = '/^\+237[26]\d{8}$/';

    /**
     * "6 90 00 00 00", "690-000-000", "00237690000000", "+237 690000000"
     *   → "+237690000000".  Anything that is not a Cameroon number → null.
     */
    public static function normalize(?string $raw): ?string
    {
        if (blank($raw)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        // Remove the country code. "00237" must be checked before "237".
        if (str_starts_with($digits, '00237')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '237') && strlen($digits) === 12) {
            $digits = substr($digits, 3);
        }

        // 9 digits, starting with 6 (mobile) or 2 (landline)
        return preg_match('/^[26]\d{8}$/', $digits) ? '+237' . $digits : null;
    }

    /** A hash of the number, for cache keys and logs (never store the raw number there). */
    public static function fingerprint(string $phone): string
    {
        return hash_hmac('sha256', $phone, (string) config('app.key'));
    }
}
