<?php
// app/Repositories/LocationTracking/LocationTrackingHostRepository.php

namespace App\Repositories\LocationTracking;

use App\Models\Booking;
use App\Models\BookingLocation;
use App\Repositories\LocationTracking\Concerns\CalculatesDistance;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingHostRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LocationTrackingHostRepository implements LocationTrackingHostRepositoryInterface
{
    use CalculatesDistance;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function updateLocation(int $bookingId, int $hostId, float $lat, float $lng): array
    {
        return DB::transaction(function () use ($bookingId, $hostId, $lat, $lng) {

            // ← hostId جاي من auth() بالكونترولر مش من المستخدم، فما
            // ممكن حدا يحدّث موقع حجز مش تبعو
            $booking = Booking::where('host_id', $hostId)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if ($booking->delivery_type !== 'delivery') {
                return ['status' => 'not_delivery_booking'];
            }

            if (!in_array($booking->status, ['confirmed', 'active'])) {
                return ['status' => 'not_trackable'];
            }

            $location = BookingLocation::firstOrNew(['booking_id' => $booking->id]);
            $location->host_lat        = $lat;
            $location->host_lng        = $lng;
            $location->host_updated_at = now();
            $location->save();

            $distanceKm = $this->distanceInKm(
                $lat, $lng,
                $location->guest_lat ? (float) $location->guest_lat : null,
                $location->guest_lng ? (float) $location->guest_lng : null,
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
    public function getLocation(int $bookingId, int $hostId): array
    {
        $booking = Booking::where('host_id', $hostId)->findOrFail($bookingId);

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
