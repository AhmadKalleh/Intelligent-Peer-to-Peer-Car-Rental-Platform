<?php
// app/Repositories/LocationTracking/Interfaces/LocationTrackingHostRepositoryInterface.php

namespace App\Repositories\LocationTracking\Interfaces;

interface LocationTrackingHostRepositoryInterface
{
    /**
     * المالك يحدّث موقعو الحالي وهو عم يوصّل/يستلم السيارة.
     */
    public function updateLocation(int $bookingId, int $hostId, float $lat, float $lng): array;

    /**
     * جلب آخر حالة تتبع معروفة للحجز (لفتح الخريطة أول مرة).
     */
    public function getLocation(int $bookingId, int $hostId): array;
}
