<?php
// app/Services/LocationTracking/LocationTrackingGuestService.php

namespace App\Services\LocationTracking;

use App\Jobs\BroadcastBookingLocationUpdatedJob;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingGuestRepositoryInterface;

class LocationTrackingGuestService
{
    public function __construct(
        protected LocationTrackingGuestRepositoryInterface $_locationTrackingGuestRepository,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE LOCATION
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function updateLocation(int $bookingId, int $userId, float $lat, float $lng): array
    {
        $result = $this->_locationTrackingGuestRepository->updateLocation($bookingId, $userId, $lat, $lng);

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
            role      : 'guest',
            lat       : $lat,
            lng       : $lng,
            hostLat   : $location->host_lat ? (float) $location->host_lat : null,
            hostLng   : $location->host_lng ? (float) $location->host_lng : null,
            guestLat  : $lat,
            guestLng  : $lng,
            distanceKm: $result['distance_km'],
        );

        return [
            'data' => [
                'host' => [
                    'lat'        => $location->host_lat ? (float) $location->host_lat : null,
                    'lng'        => $location->host_lng ? (float) $location->host_lng : null,
                    'updated_at' => $location->host_updated_at,
                ],
                'guest' => [
                    'lat'        => (float) $location->guest_lat,
                    'lng'        => (float) $location->guest_lng,
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
    public function getLocation(int $bookingId, int $userId): array
    {
        $result = $this->_locationTrackingGuestRepository->getLocation($bookingId, $userId);

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
