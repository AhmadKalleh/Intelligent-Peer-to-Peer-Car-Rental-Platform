<?php
// app/Http/Controllers/Api/LocationTracking/GuestLocationTrackingController.php

namespace App\Http\Controllers\Api\LocationTracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationTrackingRequests\FormRequestGuestLocationTracking;
use App\Services\LocationTracking\LocationTrackingGuestService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestLocationTrackingController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected LocationTrackingGuestService $_locationTrackingGuestService
    ) {}

    // ─── POST /api/Guest/bookings/track-location ───────────────
    public function update(FormRequestGuestLocationTracking $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            // ← userId دايمًا من auth()، أبدًا من input المستخدم
            $result = $this->_locationTrackingGuestService->updateLocation(
                $validated['booking_id'],
                auth()->id(),
                (float) $validated['lat'],
                (float) $validated['lng'],
            );

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── GET /api/Guest/bookings/track-location ────────────────
    public function show(FormRequestGuestLocationTracking $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_locationTrackingGuestService->getLocation(
                $request->validated()['booking_id'],
                auth()->id(),
            );

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
