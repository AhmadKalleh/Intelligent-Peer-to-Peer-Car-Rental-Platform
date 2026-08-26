<?php
// app/Repositories/Coupon/CouponGuestRepository.php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use App\Models\CouponUse;
use App\Repositories\Coupon\Interfaces\CouponGuestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CouponGuestRepository implements CouponGuestRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // VALIDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function validate(string $code, int $userId, int $totalDays): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        // ── الكوبون غير موجود ────────────────────────────
        if (!$coupon) {
            return ['status' => 'not_found'];
        }

        // ── الكوبون غير نشط ──────────────────────────────
        if (!$coupon->is_active) {
            return ['status' => 'inactive'];
        }

        // ── لم يبدأ بعد ──────────────────────────────────
        if (now()->lt($coupon->valid_from)) {
            return ['status' => 'not_started'];
        }

        // ── منتهي الصلاحية ────────────────────────────────
        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            return ['status' => 'expired'];
        }

        // ── وصل الحد الأقصى للاستخدام ────────────────────
        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            return ['status' => 'exhausted'];
        }

        // ── المستخدم استخدمه من قبل ──────────────────────
        $alreadyUsed = CouponUse::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyUsed) {
            return ['status' => 'already_used'];
        }

        // ── لا يطابق الحد الأدنى للأيام ──────────────────
        if ($totalDays < $coupon->min_booking_days) {
            return [
                'status'           => 'min_days_not_met',
                'min_booking_days' => $coupon->min_booking_days,
            ];
        }

        return ['status' => 'valid', 'coupon' => $coupon];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // APPLY (يُستدعى داخل إنشاء الحجز)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function apply(int $couponId, int $userId, int $bookingId, float $subtotal): void
    {
        DB::transaction(function () use ($couponId, $userId, $bookingId, $subtotal) {

            $coupon = Coupon::lockForUpdate()->findOrFail($couponId);

            $discountAmount = $coupon->calculateDiscount($subtotal);

            // ── سجل الاستخدام ────────────────────────────
            CouponUse::create([
                'coupon_id'        => $couponId,
                'user_id'          => $userId,
                'booking_id'       => $bookingId,
                'discount_applied' => $discountAmount,
                'used_at'          => now(),
            ]);

            // ── زيادة عداد الاستخدام ──────────────────────
            $coupon->increment('used_count');
        });
    }
}
