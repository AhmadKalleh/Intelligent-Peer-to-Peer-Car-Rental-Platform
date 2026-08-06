<?php
// app/Http/Controllers/Api/Handover/GuestHandoverController.php

namespace App\Http\Controllers\Api\Handover;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandoverRequests\FormRequestGuestHandover;
use App\Services\Handover\HandoverGuestService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestHandoverController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected HandoverGuestService $_handoverGuestService
    ) {}

    // ─── POST /api/Guest/bookings/handover/confirm ────────────
    public function confirm(FormRequestGuestHandover $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            $result = $this->_handoverGuestService->confirm(
                $validated['booking_id'],
                $validated['code'],
                auth()->id()
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
