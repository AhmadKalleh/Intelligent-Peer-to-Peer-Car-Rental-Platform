<?php

namespace App\Services\Coupon;

use App\Http\Resources\Coupon\CouponHostResource;
use App\Jobs\ExpireCouponJob;
use App\Repositories\Coupon\Interfaces\CouponHostRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class CouponHostService
{
    public function __construct(
        protected CouponHostRepositoryInterface $_couponHostRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Cache Key
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function cacheKey(int $hostId): string
    {
        return "host:{$hostId}:coupons";
    }

    private function clearCache(int $hostId): void
    {
        Redis::del($this->cacheKey($hostId));
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $hostId): array
    {
        $cacheKey = $this->cacheKey($hostId);
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return [
                'data'    => json_decode($cached, true),
                'message' => 'Coupons retrieved successfully.',
                'code'    => 200,
            ];
        }

        $coupons = $this->_couponHostRepository->index($hostId);

        // ✅ نحفظ البيانات بعد تحويلها لـ Resource
        $resolved = CouponHostResource::collection($coupons)->resolve();
        Redis::setex($cacheKey, 3600, json_encode($resolved));

        return [
            'data'    => $coupons,
            'message' => 'Coupons retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // STORE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function store(int $hostId, array $data): array
    {
        $coupon = $this->_couponHostRepository->store($hostId, $data);

        if ($coupon->valid_until) {
            ExpireCouponJob::dispatch(
                couponId: $coupon->id,
                hostId  : $hostId,
            )->delay($coupon->valid_until);
        }
        // ── تحديث الكاش ───────────────────────────────────
        $this->clearCache($hostId);

        return [
            'data'    => $coupon,
            'message' => 'Coupon created successfully.',
            'code'    => 201,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function update(int $hostId, int $couponId, array $data): array
    {
        $coupon = $this->_couponHostRepository->update($hostId, $couponId, $data);

        $this->clearCache($hostId);

        if ($coupon->valid_until) {
            ExpireCouponJob::dispatch(
                couponId: $coupon->id,
                hostId  : $hostId,
            )->delay($coupon->valid_until);
        }

        return [
            'data'    => $coupon,
            'message' => 'Coupon updated successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TOGGLE STATUS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function toggleStatus(int $hostId, int $couponId): array
    {
        $coupon = $this->_couponHostRepository->toggleStatus($hostId, $couponId);

        $this->clearCache($hostId);

        return [
            'data'    => $coupon,
            'message' => $coupon->is_active
                ? 'Coupon activated successfully.'
                : 'Coupon deactivated successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DESTROY
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function destroy(int $hostId, int $couponId): array
    {
        $result = $this->_couponHostRepository->destroy($hostId, $couponId);

        if ($result['status'] === 'deleted') {
            $this->clearCache($hostId);
        }

        return match($result['status']) {
            'has_uses' => [
                'data'    => [],
                'message' => 'Cannot delete a coupon that has been used. Deactivate it instead.',
                'code'    => 422,
            ],
            'deleted'  => [
                'data'    => [],
                'message' => 'Coupon deleted successfully.',
                'code'    => 200,
            ],
        };
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SHOW USES (لا يحتاج كاش لأنه بيانات متغيرة)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function showUses(int $hostId, int $couponId): array
    {
        $coupon = $this->_couponHostRepository->showUses($hostId, $couponId);

        return [
            'data'    => $coupon,
            'message' => 'Coupon uses retrieved successfully.',
            'code'    => 200,
        ];
    }
}
