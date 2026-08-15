<?php
// app/Services/LocationTracking/LocationTrackingHostService.php

namespace App\Services\LocationTracking;

use App\Jobs\BroadcastBookingLocationUpdatedJob;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingHostRepositoryInterface;

class LocationTrackingHostService
{
    public function __construct(
        protected LocationTrackingHostRepositoryInterface $_locationTrackingHostRepository,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function updateLocation(int $bookingId, int $hostId, float $lat, float $lng): array
    {
        $result = $this->_locationTrackingHostRepository->updateLocation($bookingId, $hostId, $lat, $lng);

        if ($result['status'] === 'not_delivery_booking') {
            return [
                'data'    => [],
                'message' => 'Live tracking is only available for delivery bookings.',
                'code'    => 422,
            ];
        }

        if ($result['status'] === 'not_trackable') {
            return [
                'data'    => [],
                'message' => 'This booking is not in a trackable state right now.',
                'code'    => 422,
            ];
        }

        $location = $result['location'];

        // ── بث لحظي لكل من يستمع على قناة هالحجز ────────────
        BroadcastBookingLocationUpdatedJob::dispatch(
            bookingId : $bookingId,
            role      : 'host',
            lat       : $lat,
            lng       : $lng,
            hostLat   : $lat,
            hostLng   : $lng,
            guestLat  : $location->guest_lat ? (float) $location->guest_lat : null,
            guestLng  : $location->guest_lng ? (float) $location->guest_lng : null,
            distanceKm: $result['distance_km'],
        );

        return [
            'data' => [
                'host' => [
                    'lat'        => (float) $location->host_lat,
                    'lng'        => (float) $location->host_lng,
                    'updated_at' => $location->host_updated_at,
                ],
                'guest' => [
                    'lat'        => $location->guest_lat ? (float) $location->guest_lat : null,
                    'lng'        => $location->guest_lng ? (float) $location->guest_lng : null,
                    'updated_at' => $location->guest_updated_at,
                ],
                'distance_km' => $result['distance_km'],
            ],
            'message' => 'Location updated successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // GET LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getLocation(int $bookingId, int $hostId): array
    {
        $result = $this->_locationTrackingHostRepository->getLocation($bookingId, $hostId);

        if ($result['status'] === 'not_delivery_booking') {
            return [
                'data'    => [],
                'message' => 'Live tracking is only available for delivery bookings.',
                'code'    => 422,
            ];
        }

        $booking  = $result['booking'];
        $location = $result['location'];

        return [
            'data' => [
                'booking_status'   => $booking->status,
                'delivery_address' => $booking->delivery_address,
                'host' => [
                    'lat'        => $location?->host_lat ? (float) $location->host_lat : null,
                    'lng'        => $location?->host_lng ? (float) $location->host_lng : null,
                    'updated_at' => $location?->host_updated_at,
                ],
                'guest' => [
                    'lat'        => $location?->guest_lat ? (float) $location->guest_lat : null,
                    'lng'        => $location?->guest_lng ? (float) $location->guest_lng : null,
                    'updated_at' => $location?->guest_updated_at,
                ],
                'distance_km' => $result['distance_km'],
            ],
            'message' => 'Tracking info retrieved successfully.',
            'code'    => 200,
        ];
    }
}
