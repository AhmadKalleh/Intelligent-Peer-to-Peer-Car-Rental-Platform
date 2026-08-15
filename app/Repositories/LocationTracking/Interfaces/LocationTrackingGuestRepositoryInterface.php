<?php
// app/Repositories/LocationTracking/Interfaces/LocationTrackingGuestRepositoryInterface.php

namespace App\Repositories\LocationTracking\Interfaces;

interface LocationTrackingGuestRepositoryInterface
{
    /**
     * المستأجر يحدّث موقعو الحالي (مكان انتظار السيارة).
     */
    public function updateLocation(int $bookingId, int $userId, float $lat, float $lng): array;

    /**
     * جلب آخر حالة تتبع معروفة للحجز (لفتح الخريطة أول مرة).
     */
    public function getLocation(int $bookingId, int $userId): array;
}
