<?php
// app/Http/Controllers/Api/Coupon/HostCouponController.php

namespace App\Http\Controllers\Api\Coupon;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequests\FormRequestHostCoupon;
use App\Http\Resources\Coupon\CouponHostResource;
use App\Http\Resources\Coupon\CouponUseResource;
use App\Services\Coupon\CouponHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostCouponController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected CouponHostService $_couponHostService
    ) {}

    // ─── GET /api/host/coupons ────────────────────────────────
    public function index(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->index($hostId);

            // ✅ دائماً نفس الشكل سواء من كاش أو DB
            $coupons = is_array($result['data'])
                ? $result['data']
                : CouponHostResource::collection($result['data']);

            return $this->Success($coupons, $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── POST /api/host/coupons ───────────────────────────────
    public function store(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->store($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── PUT /api/host/coupons/{id} ───────────────────────────
    public function update(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->update($hostId, $request->input('coupon_id'), $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── PUT /api/host/coupons/{id}/toggle ───────────────────
    public function toggleStatus(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->toggleStatus($hostId, $request->input('coupon_id'));

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── DELETE /api/host/coupons/{id} ───────────────────────
    public function destroy(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->destroy($hostId, $request->input('coupon_id'));

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── GET /api/host/coupons/{id}/uses ─────────────────────
    public function showUses(FormRequestHostCoupon $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_couponHostService->showUses($hostId, $request->input('coupon_id'));

            return $this->Success([
                'coupon' => new CouponHostResource($result['data']),
                'uses'   => CouponUseResource::collection($result['data']->uses),
            ], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
