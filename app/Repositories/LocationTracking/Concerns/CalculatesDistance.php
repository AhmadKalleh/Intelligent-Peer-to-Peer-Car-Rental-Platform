<?php
// app/Repositories/LocationTracking/Concerns/CalculatesDistance.php

namespace App\Repositories\LocationTracking\Concerns;

trait CalculatesDistance
{
    /**
     * يحسب المسافة بالكيلومتر بين نقطتين جغرافيتين (صيغة Haversine).
     */
    protected function distanceInKm(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $earthRadiusKm = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }
}
