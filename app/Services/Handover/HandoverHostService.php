<?php
// app/Services/Handover/HandoverHostService.php

namespace App\Services\Handover;

use App\Repositories\Handover\Interfaces\HandoverHostRepositoryInterface;
use App\Services\Notification\NotificationService;

class HandoverHostService
{
    public function __construct(
        protected HandoverHostRepositoryInterface $_handoverHostRepository,
        protected NotificationService             $_notificationService,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // GENERATE (المالك يولّد كود/QR الاستلام أو التسليم)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function generate(int $bookingId, int $hostId): array
    {
        $result = $this->_handoverHostRepository->generate($bookingId, $hostId);

        if ($result['status'] === 'not_eligible') {
            return [
                'data'    => [],
                'message' => 'This booking is not ready for pickup or return right now.',
                'code'    => 422,
            ];
        }

        $handover = $result['handover'];
        $isPickup = $result['type'] === 'pickup';

        // ── إشعار المستأجر بأن الكود صار جاهز ──────────────
        $this->_notificationService->send(
            userId : $handover->booking->user_id,
            type   : $isPickup ? 'pickup_code_ready' : 'return_code_ready',
            title  : $isPickup ? 'Ready for Pickup' : 'Ready for Return',
            body   : 'Ask the host to show you the QR code, then scan it to confirm.',
        );

        return [
            'data' => [
                'booking_id' => $bookingId,
                'type'       => $result['type'],
                // الكود الحقيقي يُرسل مرة واحدة فقط هنا ليعرضه المالك كـ QR
                'code'       => $result['code'],
                'expires_at' => $handover->expires_at,
            ],
            'message' => 'Handover code generated successfully.',
            'code'    => 200,
        ];
    }
}
