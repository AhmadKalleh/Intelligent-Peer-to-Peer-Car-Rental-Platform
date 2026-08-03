<?php
// app/Repositories/Booking/BookingHostRepository.php

namespace App\Repositories\Booking;

use App\Models\Booking;
use App\Repositories\Booking\Interfaces\BookingHostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingHostRepository implements BookingHostRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $hostId, ?string $status, int $perPage): LengthAwarePaginator
    {
        return Booking::with(['vehicle', 'user', 'payment'])
            ->where('host_id', $hostId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    
}
