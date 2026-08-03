<?php
// app/Services/Booking/BookingHostService.php

namespace App\Services\Booking;

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

    
}
