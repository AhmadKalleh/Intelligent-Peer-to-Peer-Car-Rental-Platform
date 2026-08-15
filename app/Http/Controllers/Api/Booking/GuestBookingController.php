<?php
// app/Http/Controllers/Api/Booking/GuestBookingController.php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequests\FormRequestGuestBooking;
use App\Http\Resources\Booking\BookingGuestResource;
use App\Services\Booking\BookingGuestService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GuestBookingController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected BookingGuestService $_bookingGuestService
    ) {}

    // ─── POST /api/bookings/calculate ────────────────────────
    public function calculatePrice(FormRequestGuestBooking $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->calculatePrice($request->validated());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── POST /api/bookings ───────────────────────────────────
    public function createBooking(FormRequestGuestBooking $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->createBooking($request->validated());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── POST /api/payments/webhook (بدون auth) ───────────────
    public function handleWebhook(Request $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->handleWebhook($request->all());
            return $this->Success($data, 'Webhook processed.', 200);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── DELETE /api/bookings/{id} ────────────────────────────
    public function cancelBooking(FormRequestGuestBooking $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->cancelBooking($request->validated()['booking_id']);
            return $this->Success(
                $result['data'] ? new BookingGuestResource($result['data']) : [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }


    // ─── GET /api/bookings ────────────────────────────────────
    public function index(FormRequestGuestBooking $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->index($request->validated());
            return $this->Success([
                'bookings'   => BookingGuestResource::collection($result['data']),
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

    // ─── GET /api/Guest/bookings/current ← جديد ────────────────
    // يرجّع الحجز الحالي (اللي بحاجة استلام أو تسليم الآن) للمستأجر
    public function getActiveBooking(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->getActiveBooking();
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function getConfirmedBooking(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_bookingGuestService->getConfirmedBooking();
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }


}
