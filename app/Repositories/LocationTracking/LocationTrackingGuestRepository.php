<?php
// app/Repositories/LocationTracking/LocationTrackingGuestRepository.php

namespace App\Repositories\LocationTracking;

use App\Models\Booking;
use App\Models\BookingLocation;
use App\Repositories\LocationTracking\Concerns\CalculatesDistance;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingGuestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LocationTrackingGuestRepository implements LocationTrackingGuestRepositoryInterface
{
    use CalculatesDistance;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function updateLocation(int $bookingId, int $userId, float $lat, float $lng): array
    {
        return DB::transaction(function () use ($bookingId, $userId, $lat, $lng) {

            // ← userId جاي من auth() بالكونترولر مش من المستخدم، فما
            // ممكن حدا يحدّث موقع حجز مش تبعو
            $booking = Booking::where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if ($booking->delivery_type !== 'delivery') {
                return ['status' => 'not_delivery_booking'];
            }

            if (!in_array($booking->status, ['confirmed', 'active'])) {
                return ['status' => 'not_trackable'];
            }

            $location = BookingLocation::firstOrNew(['booking_id' => $booking->id]);
            $location->guest_lat        = $lat;
            $location->guest_lng        = $lng;
            $location->guest_updated_at = now();
            $location->save();

            $distanceKm = $this->distanceInKm(
                $location->host_lat ? (float) $location->host_lat : null,
                $location->host_lng ? (float) $location->host_lng : null,
                $lat, $lng,
            );

            return [
                'status'      => 'updated',
                'location'    => $location,
                'distance_km' => $distanceKm,
            ];
        });
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // GET LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getLocation(int $bookingId, int $userId): array
    {
        $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);

        if ($booking->delivery_type !== 'delivery') {
            return ['status' => 'not_delivery_booking'];
        }

        $location = BookingLocation::where('booking_id', $booking->id)->first();

        $distanceKm = $location
            ? $this->distanceInKm(
                $location->host_lat  ? (float) $location->host_lat  : null,
                $location->host_lng  ? (float) $location->host_lng  : null,
                $location->guest_lat ? (float) $location->guest_lat : null,
                $location->guest_lng ? (float) $location->guest_lng : null,
              )
            : null;

        return [
            'status'      => 'found',
            'booking'     => $booking,
            'location'    => $location,
            'distance_km' => $distanceKm,
        ];
    }
}
