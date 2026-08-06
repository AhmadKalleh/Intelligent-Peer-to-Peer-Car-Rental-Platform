<?php
// app/Services/Booking/BookingHostService.php

namespace App\Services\Booking;

use App\Http\Resources\Booking\BookingHostResource;
use App\Repositories\Booking\Interfaces\BookingHostRepositoryInterface;

class BookingHostService
{
    public function __construct(
        protected BookingHostRepositoryInterface $_bookingHostRepository,
    ) {}

    public function index(int $hostId, array $filters): array
    {
        $result = $this->_bookingHostRepository->index(
            hostId  : $hostId,
            status  : $filters['status']   ?? null,
            perPage : $filters['per_page'] ?? 10,
        );

        return [
            'data'    => $result,
            'message' => 'Bookings retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CURRENT BOOKING ← جديد
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function currentBooking(int $hostId): array
    {
        $booking = $this->_bookingHostRepository->currentBooking($hostId);

        if (!$booking) {
            return [
                'data'    => null,
                'message' => 'No current booking right now.',
                'code'    => 200,
            ];
        }

        return [
            'data'    => new BookingHostResource($booking),
            'message' => 'Current booking retrieved successfully.',
            'code'    => 200,
        ];
    }

}
