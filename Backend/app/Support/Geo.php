<?php

namespace App\Support;

class Geo
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Distance in km between two GPS points (Haversine formula).
     * Good enough for a city or a country: error under 0.5%.
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Validation rules for a latitude inside Cameroon. */
    public static function latRules(bool $required = true): array
    {
        [$min, $max] = config('safeme.bounds.lat');

        return [$required ? 'required' : 'nullable', 'numeric', "between:$min,$max"];
    }

    /** Validation rules for a longitude inside Cameroon. */
    public static function lngRules(bool $required = true): array
    {
        [$min, $max] = config('safeme.bounds.lng');

        return [$required ? 'required' : 'nullable', 'numeric', "between:$min,$max"];
    }
}
