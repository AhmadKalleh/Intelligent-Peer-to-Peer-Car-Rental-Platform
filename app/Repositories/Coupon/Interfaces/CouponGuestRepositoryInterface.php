<?php
// app/Repositories/Coupon/Interfaces/CouponGuestRepositoryInterface.php

namespace App\Repositories\Coupon\Interfaces;

use App\Models\Coupon;

interface CouponGuestRepositoryInterface
{
    public function validate(string $code, int $userId, int $totalDays): array;
    public function apply(int $couponId, int $userId, int $bookingId, float $subtotal): void;
}
