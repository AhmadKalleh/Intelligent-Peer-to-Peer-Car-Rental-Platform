<?php
// app/Http/Controllers/Api/Booking/HostBookingController.php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequests\FormRequestHostBooking;
use App\Http\Resources\Booking\BookingHostResource;
use App\Services\Booking\BookingHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostBookingController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected BookingHostService $_bookingHostService
    ) {}

    // ─── GET /api/host/bookings ───────────────────────────────
    public function index(FormRequestHostBooking $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_bookingHostService->index($hostId, $request->validated());

            return $this->Success([
                'bookings'   => BookingHostResource::collection($result['data']),
                'pagination' => [
                    'current_page'  => $result['data']->currentPage(),
                    'last_page'     => $result['data']->lastPage(),
                    'per_page'      => $result['data']->perPage(),
                    'total'         => $result['data']->total(),
                    'next_page_url' => $result['data']->nextPageUrl(),
                    'prev_page_url' => $result['data']->previousPageUrl(),
                ],
            ], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── GET /api/host/bookings/current ← جديد ─────────────────
    // يرجّع الحجز الحالي (اللي بحاجة استلام أو تسليم الآن) للمالك
    public function currentBooking(): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_bookingHostService->currentBooking($hostId);
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    
}
