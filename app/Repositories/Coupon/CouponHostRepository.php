<?php
// app/Repositories/Coupon/CouponHostRepository.php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use App\Repositories\Coupon\Interfaces\CouponHostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CouponHostRepository implements CouponHostRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $hostId): Collection
    {
        return Coupon::where('host_id', $hostId)
            ->orderByDesc('created_at')
            ->get();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // STORE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function store(int $hostId, array $data): Coupon
    {
        return Coupon::create([
            'host_id'          => $hostId,
            'code'             => strtoupper($data['code']),
            'discount_type'    => $data['discount_type'],
            'discount_value'   => $data['discount_value'],
            'min_booking_days' => $data['min_booking_days'] ?? 1,
            'max_uses'         => $data['max_uses']         ?? null,
            'is_active'        => true,
            'valid_from'       => $data['valid_from']       ?? now(),
            'valid_until'      => $data['valid_until']      ?? null,
        ]);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function update(int $hostId, int $couponId, array $data): Coupon
    {
        $coupon = Coupon::where('host_id', $hostId)->findOrFail($couponId);

        $coupon->update(array_filter([
            'code'             => isset($data['code'])
                                    ? strtoupper($data['code'])
                                    : $coupon->code,
            'discount_type'    => $data['discount_type']    ?? $coupon->discount_type,
            'discount_value'   => $data['discount_value']   ?? $coupon->discount_value,
            'min_booking_days' => $data['min_booking_days'] ?? $coupon->min_booking_days,
            'max_uses'         => array_key_exists('max_uses', $data)
                                    ? $data['max_uses']
                                    : $coupon->max_uses,
            'valid_from'       => $data['valid_from']       ?? $coupon->valid_from,
            'valid_until'      => array_key_exists('valid_until', $data)
                                    ? $data['valid_until']
                                    : $coupon->valid_until,
        ], fn($v) => $v !== null));

        return $coupon->fresh();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TOGGLE STATUS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function toggleStatus(int $hostId, int $couponId): Coupon
    {
        $coupon = Coupon::where('host_id', $hostId)->findOrFail($couponId);

        $coupon->update(['is_active' => !$coupon->is_active]);

        return $coupon->fresh();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DESTROY
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function destroy(int $hostId, int $couponId): array
    {
        $coupon = Coupon::where('host_id', $hostId)->findOrFail($couponId);

        // ── لا يمكن حذف كوبون استُخدم من قبل ───────────
        if ($coupon->used_count > 0) {
            return ['status' => 'has_uses'];
        }

        $coupon->delete();

        return ['status' => 'deleted'];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SHOW USES
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function showUses(int $hostId, int $couponId): Coupon
    {
        return Coupon::where('host_id', $hostId)
            ->with([
                'uses.user:id,full_name,email',
                'uses.booking:id,start_date,end_date,total_amount,status',
            ])
            ->findOrFail($couponId);
    }
}
