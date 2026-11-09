<?php

namespace App\Support;

use Illuminate\Support\Str;

class Text
{
    /** Trim, collapse spaces, remove HTML tags. Used on names shown on posters. */
    public static function clean(mixed $value): mixed
    {
        return is_string($value)
            ? preg_replace('/\s+/u', ' ', trim(strip_tags($value)))
            : $value;
    }

    /**
     * Turns a person's name into a "matching key", so the same name written
     * differently gives the same key:
     *   "TEMGOUA Anantia", "anantia  temgoua", "Témgoua Anantia" → "anantia temgoua"
     * Steps: remove accents, lowercase, keep letters only, sort the words.
     */
    public static function nameKey(string $name): string
    {
        $ascii = Str::lower(Str::ascii($name));
        $words = preg_split('/[^a-z]+/', $ascii, -1, PREG_SPLIT_NO_EMPTY);
        sort($words);

        return implode(' ', $words);
    }

    /** "Anantia Temgoua" → "A****** T******" : enough to recognise, not enough to steal. */
    public static function maskName(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->map(fn (string $word) => mb_substr($word, 0, 1) . str_repeat('*', max(mb_strlen($word) - 1, 1)))
            ->implode(' ');
    }
}
