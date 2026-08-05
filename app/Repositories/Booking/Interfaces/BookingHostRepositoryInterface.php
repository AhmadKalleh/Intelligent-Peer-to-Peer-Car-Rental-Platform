<?php
// app/Repositories/Booking/Interfaces/BookingHostRepositoryInterface.php

namespace App\Repositories\Booking\Interfaces;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingHostRepositoryInterface
{
    public function index(int $hostId, ?string $status, int $perPage): LengthAwarePaginator;
}
