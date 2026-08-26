<?php
// app/Repositories/Handover/HandoverGuestRepository.php

namespace App\Repositories\Handover;

use App\Models\Booking;
use App\Models\VehicleHandover;
use App\Repositories\Handover\Interfaces\HandoverGuestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class HandoverGuestRepository implements HandoverGuestRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CONFIRM
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function confirm(int $bookingId, string $code, int $userId): array
    {
        return DB::transaction(function () use ($bookingId, $code, $userId) {

            $booking = Booking::where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            $handover = VehicleHandover::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->where('code_hash', hash('sha256', strtoupper($code)))
                ->where('expires_at', '>=', now())
                ->lockForUpdate()
                ->first();

            if (!$handover) {
                return ['status' => 'invalid_code'];
            }

            $handover->update([
                'status'           => 'confirmed',
                'guest_scanned_at' => now(),
            ]);

            // ── تحديث حالة الحجز حسب نوع العملية ───────────────
            if ($handover->type === 'pickup') {
                $booking->update([
                    'status'       => 'active',
                    'picked_up_at' => now(),
                ]);
            } else {
                $booking->update([
                    'status'      => 'completed',
                    'returned_at' => now(),
                ]);
            }

            return [
                'status'   => 'confirmed',
                'handover' => $handover->fresh(),
                'booking'  => $booking->fresh(['vehicle', 'host.user', 'payment']),
            ];
        });
    }
}
