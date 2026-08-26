<?php
// app/Repositories/Handover/HandoverHostRepository.php

namespace App\Repositories\Handover;

use App\Models\Booking;
use App\Models\VehicleHandover;
use App\Repositories\Handover\Interfaces\HandoverHostRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandoverHostRepository implements HandoverHostRepositoryInterface
{
    // مدة صلاحية الكود بالدقائق
    private const CODE_TTL_MINUTES = 10;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // GENERATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function generate(int $bookingId, int $hostId): array
    {
        return DB::transaction(function () use ($bookingId, $hostId) {

            $booking = Booking::where('host_id', $hostId)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            // ── تحديد نوع العملية حسب حالة الحجز الحالية ──────
            $type = match ($booking->status) {
                'confirmed' => 'pickup',   // بداية الحجز → استلام السيارة
                'active'    => 'return',   // نهاية الحجز → تسليم السيارة
                default     => null,
            };

            if (!$type) {
                return ['status' => 'not_eligible'];
            }

            // ── إلغاء أي كود سابق لنفس النوع لسا معلّق ────────
            VehicleHandover::where('booking_id', $booking->id)
                ->where('type', $type)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            // ── توليد كود جديد ─────────────────────────────────
            $code = strtoupper(Str::random(8));

            $handover = VehicleHandover::create([
                'booking_id'        => $booking->id,
                'type'              => $type,
                'code_hash'         => hash('sha256', $code),
                'status'            => 'pending',
                'host_generated_at' => now(),
                'expires_at'        => now()->addMinutes(self::CODE_TTL_MINUTES),
            ]);

            return [
                'status'   => 'generated',
                'handover' => $handover,
                'code'     => $code,
                'type'     => $type,
            ];
        });
    }
}
