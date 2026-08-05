<?php
// app/Services/Notification/NotificationService.php

namespace App\Services\Notification;

use App\Http\Resources\Notification\NotificationResource;
use App\Jobs\BroadcastNotificationJob;
use App\Repositories\Notification\Interfaces\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $_notificationRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $userId, int $perPage): array
    {
        $result = $this->_notificationRepository->index($userId, $perPage);

        return [
            'data' => [
                'unread'     => NotificationResource::collection($result['unread']),
                'read'       => NotificationResource::collection($result['read']),
                'pagination' => [
                    'current_page'  => $result['read']->currentPage(),
                    'last_page'     => $result['read']->lastPage(),
                    'per_page'      => $result['read']->perPage(),
                    'total'         => $result['read']->total(),
                    'next_page_url' => $result['read']->nextPageUrl(),
                    'prev_page_url' => $result['read']->previousPageUrl(),
                ],
            ],
            'message' => 'Notifications retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MARK ALL AS READ
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function markAllAsRead(int $userId, array $ids): array
    {
        $updated = $this->_notificationRepository->markAllAsRead($userId, $ids);

        return [
            'data'    => ['updated_count' => $updated],
            'message' => "{$updated} notification(s) marked as read.",
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UNREAD COUNT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function unreadCount(int $userId): array
    {
        $count = $this->_notificationRepository->unreadCount($userId);

        return [
            'data'    => ['unread_count' => $count],
            'message' => 'Unread count retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEND (خدمة داخلية تُستدعى من أي مكان)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function send(
        int     $userId,
        string  $type,
        string  $title,
        ?string $body           = null,
    ): void {
        // ── حفظ في DB ─────────────────────────────────────
        $this->_notificationRepository->send(
            userId         : $userId,
            type           : $type,
            title          : $title,
            body           : $body,
        );

        // ── Broadcast في الخلفية ──────────────────────────
        BroadcastNotificationJob::dispatch(
            userId : $userId,
            type   : $type,
            title  : $title,
            body   : $body,
        );
    }
}
