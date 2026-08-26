<?php
// app/Http/Controllers/Api/Notification/NotificationController.php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequests\FormRequestNotification;
use App\Services\Notification\NotificationService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class NotificationController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected NotificationService $_notificationService
    ) {}

    // ─── GET /api/notifications ───────────────────────────────
    public function index(FormRequestNotification $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_notificationService->index(
                userId  : auth()->id(),
                perPage : $request->get('per_page', 10),
            );

            return $this->Success($result['data'], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── PUT /api/notifications/mark-all-read ─────────────────
    public function markAllAsRead(FormRequestNotification $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_notificationService->markAllAsRead(
                userId : auth()->id(),
                ids    : $request->validated()['ids'],
            );

            return $this->Success($result['data'], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── GET /api/notifications/unread-count ──────────────────
    public function unreadCount(FormRequestNotification $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_notificationService->unreadCount(auth()->id());

            return $this->Success($result['data'], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
