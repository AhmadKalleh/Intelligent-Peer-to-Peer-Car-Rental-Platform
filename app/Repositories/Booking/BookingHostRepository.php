<?php
// app/Repositories/Booking/BookingHostRepository.php

namespace App\Repositories\Booking;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
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

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Active BOOKING ← جديد
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getActiveBookings(int $hostId): ?Collection
    {
        return Booking::with([
                'vehicle',
                'user',
                'payment',
                'handovers' => fn($q) => $q->where('status', 'pending')->latest(),
            ])
            ->where('host_id', $hostId)
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->get();
    }

    public function getConfirmedBookings(int $hostId): ?Collection
    {
        return Booking::with([
                'vehicle',
                'user',
                'payment',
                'handovers' => fn($q) => $q->where('status', 'pending')->latest(),
            ])
            ->where('host_id', $hostId)
            ->where('status', 'confirmed')
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->get();
    }
}
