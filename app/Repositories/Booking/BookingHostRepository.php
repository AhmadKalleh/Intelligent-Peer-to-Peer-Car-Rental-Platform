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

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CURRENT BOOKING ← جديد
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // يرجّع الحجز الأقرب حاليًا الذي يحتاج استلام (confirmed)
    // أو تسليم (active)، أي "الحجز الحالي" الذي يظهر للمالك.
    public function currentBooking(int $hostId): ?Booking
    {
        return Booking::with([
                'vehicle',
                'user',
                'payment',
                'handovers' => fn($q) => $q->where('status', 'pending')->latest(),
            ])
            ->where('host_id', $hostId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->first();
    }
}
