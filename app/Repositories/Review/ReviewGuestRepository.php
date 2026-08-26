<?php
// app/Repositories/Review/ReviewGuestRepository.php

namespace App\Repositories\Review;

use App\Models\Booking;
use App\Models\Review;
use App\Repositories\Review\Interfaces\ReviewGuestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReviewGuestRepository implements ReviewGuestRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SUBMIT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function submit(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {

            $booking = Booking::where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($data['booking_id']);

            // ── التقييم مسموح فقط بعد انتهاء الرحلة ────────────
            if ($booking->status !== 'completed') {
                return ['status' => 'not_completed'];
            }

            // ── حجز واحد = تقييم واحد فقط ────────────────────
            if ($booking->review) {
                return ['status' => 'already_reviewed'];
            }

            $review = Review::create([
                'booking_id'            => $booking->id,
                'vehicle_id'            => $booking->vehicle_id,
                'host_id'               => $booking->host_id,
                'user_id'               => $userId,
                'overall_rating'        => $data['overall_rating'],
                'comment'               => $data['comment'] ?? null,
                'is_visible'            => true,
                'cleanliness_rating'    => $data['cleanliness_rating']   ?? null,
                'maintenance_rating'    => $data['maintenance_rating']   ?? null,
                'comfort_rating'        => $data['comfort_rating']       ?? null,
                'communication_rating'  => $data['communication_rating'] ?? null,
                'punctuality_rating'    => $data['punctuality_rating']   ?? null,
            ]);

            // ── تحديث متوسط تقييم الهوست المخزَّن (cache) ──────
            $hostAverage = Review::where('host_id', $booking->host_id)
                ->where('is_visible', true)
                ->avg('overall_rating');

            $booking->host->update([
                'rating_avg' => $hostAverage ? round((float) $hostAverage, 2) : 0,
            ]);

            // ── تحديث متوسط تقييم السيارة المخزَّن (cache) ← جديد ──
            // vehicles.rating_avg و vehicles.total_reviews منفصلين
            // عن hosts.rating_avg، وعليهم بيعتمد VehicleGuestShowResource
            // بصفحة تفاصيل السيارة (show).
            $vehicleStats = Review::where('vehicle_id', $booking->vehicle_id)
                ->where('is_visible', true)
                ->selectRaw('AVG(overall_rating) as average, COUNT(*) as total')
                ->first();

            $booking->vehicle->update([
                'rating_avg'    => $vehicleStats->average ? round((float) $vehicleStats->average, 2) : 0,
                'total_reviews' => (int) $vehicleStats->total,
            ]);

            return [
                'status' => 'created',
                'review' => $review->fresh(['host']),
            ];
        });
    }
}
