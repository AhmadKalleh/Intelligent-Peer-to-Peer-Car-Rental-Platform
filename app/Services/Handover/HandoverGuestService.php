<?php
// app/Services/Handover/HandoverGuestService.php

namespace App\Services\Handover;

use App\Http\Resources\Booking\BookingGuestResource;
use App\Repositories\Handover\Interfaces\HandoverGuestRepositoryInterface;
use App\Services\Notification\NotificationService;

class HandoverGuestService
{
    public function __construct(
        protected HandoverGuestRepositoryInterface $_handoverGuestRepository,
        protected NotificationService              $_notificationService,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CONFIRM (المستأجر يسكن كود/QR المالك)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function confirm(int $bookingId, string $code, int $userId): array
    {
        $result = $this->_handoverGuestRepository->confirm($bookingId, $code, $userId);

        if ($result['status'] === 'invalid_code') {
            return [
                'data'    => [],
                'message' => 'Invalid or expired code. Please ask the host to generate a new one.',
                'code'    => 422,
            ];
        }

        $booking  = $result['booking'];
        $isPickup = $result['handover']->type === 'pickup';

        // ── إشعار المالك ────────────────────────────────────
        $this->_notificationService->send(
            userId : $booking->host->user_id,
            type   : $isPickup ? 'vehicle_picked_up' : 'vehicle_returned',
            title  : $isPickup ? 'Vehicle Picked Up' : 'Vehicle Returned',
            body   : $isPickup
                ? 'The guest has picked up the vehicle. The trip has started.'
                : 'The guest has returned the vehicle. The trip is now completed.',
        );

        // ── إشعار المستأجر ───────────────────────────────────
        $this->_notificationService->send(
            userId : $booking->user_id,
            type   : $isPickup ? 'trip_started' : 'trip_completed',
            title  : $isPickup ? 'Trip Started!' : 'Trip Completed!',
            body   : $isPickup
                ? 'Vehicle pickup confirmed. Enjoy your trip!'
                : 'Vehicle return confirmed. Thanks for riding with us!',
        );

        return [
            'data'    => new BookingGuestResource($booking),
            'message' => $isPickup
                ? 'Vehicle pickup confirmed successfully.'
                : 'Vehicle return confirmed successfully.',
            'code'    => 200,
        ];
    }
}
