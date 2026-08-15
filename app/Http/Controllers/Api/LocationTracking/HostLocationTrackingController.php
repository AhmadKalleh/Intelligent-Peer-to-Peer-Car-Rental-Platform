<?php
// app/Http/Controllers/Api/LocationTracking/HostLocationTrackingController.php

namespace App\Http\Controllers\Api\LocationTracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationTrackingRequests\FormRequestHostLocationTracking;
use App\Services\LocationTracking\LocationTrackingHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostLocationTrackingController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected LocationTrackingHostService $_locationTrackingHostService
    ) {}

    // ─── POST /api/host/bookings/track-location ────────────────
    public function update(FormRequestHostLocationTracking $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            // ← hostId دايمًا من auth()، أبدًا من input المستخدم
            $hostId = auth()->user()->host->id;

            $result = $this->_locationTrackingHostService->updateLocation(
                $validated['booking_id'],
                $hostId,
                (float) $validated['lat'],
                (float) $validated['lng'],
            );

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── GET /api/host/bookings/track-location ─────────────────
    public function show(FormRequestHostLocationTracking $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;

            $result = $this->_locationTrackingHostService->getLocation(
                $request->validated()['booking_id'],
                $hostId,
            );

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
