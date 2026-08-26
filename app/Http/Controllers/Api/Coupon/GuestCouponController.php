<?php
// app/Http/Controllers/Api/Coupon/GuestCouponController.php

namespace App\Http\Controllers\Api\Coupon;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequests\FormRequestGuestCoupon;
use App\Services\Coupon\CouponGuestService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestCouponController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected CouponGuestService $_couponGuestService
    ) {}

    // ─── POST /api/coupons/validate ───────────────────────────
    public function validateCoupon(FormRequestGuestCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_couponGuestService->validate($request->validated());

            return $this->Success(
                $result['data'],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
