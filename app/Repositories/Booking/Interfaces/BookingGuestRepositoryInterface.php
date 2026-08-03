<?php
// app/Repositories/Booking/Interfaces/BookingGuestRepositoryInterface.php

namespace App\Repositories\Booking\Interfaces;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingGuestRepositoryInterface
{
    public function calculatePrice(array $data): array;
    public function createBooking(array $data): Booking;
    public function cancelBooking(int $bookingId, int $userId, string $reason): array;
    public function index(int $userId, ?string $status, int $perPage): LengthAwarePaginator;
}
