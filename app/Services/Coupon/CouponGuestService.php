<?php
// app/Services/Coupon/CouponGuestService.php

namespace App\Services\Coupon;

use App\Http\Resources\Coupon\CouponValidateResource;
use App\Repositories\Coupon\Interfaces\CouponGuestRepositoryInterface;

class CouponGuestService
{
    public function __construct(
        protected CouponGuestRepositoryInterface $_couponGuestRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // VALIDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function validate(array $data): array
    {
        $result = $this->_couponGuestRepository->validate(
            code      : $data['code'],
            userId    : auth()->id(),
            totalDays : $data['total_days'],
        );

        return match($result['status']) {
            'not_found'       => [
                'data'    => [],
                'message' => 'Coupon not found.',
                'code'    => 404,
            ],
            'inactive'        => [
                'data'    => [],
                'message' => 'This coupon is inactive.',
                'code'    => 422,
            ],
            'not_started'     => [
                'data'    => [],
                'message' => 'This coupon is not valid yet.',
                'code'    => 422,
            ],
            'expired'         => [
                'data'    => [],
                'message' => 'This coupon has expired.',
                'code'    => 422,
            ],
            'exhausted'       => [
                'data'    => [],
                'message' => 'This coupon has reached its maximum uses.',
                'code'    => 422,
            ],
            'already_used'    => [
                'data'    => [],
                'message' => 'You have already used this coupon.',
                'code'    => 422,
            ],
            'min_days_not_met' => [
                'data'    => [],
                'message' => "This coupon requires a minimum of {$result['min_booking_days']} booking days.",
                'code'    => 422,
            ],
            'valid'           => [
                'data'    => new CouponValidateResource($result['coupon'], (float) $data['subtotal']),
                'message' => 'Coupon is valid.',
                'code'    => 200,
            ],
        };
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // APPLY (يُستدعى من Booking Service)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function apply(int $couponId, int $userId, int $bookingId, float $subtotal): void
    {
        $this->_couponGuestRepository->apply($couponId, $userId, $bookingId, $subtotal);
    }
}
