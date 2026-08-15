<?php
// app/Repositories/Booking/Interfaces/BookingHostRepositoryInterface.php

namespace App\Repositories\Booking\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingHostRepositoryInterface
{
    public function index(int $hostId, ?string $status, int $perPage): LengthAwarePaginator;

    // ← جديد: الحجز الحالي الذي يحتاج استلام/تسليم الآن
    public function getActiveBookings(int $hostId): ?Collection;
    public function getConfirmedBookings(int $hostId): ?Collection;
}
