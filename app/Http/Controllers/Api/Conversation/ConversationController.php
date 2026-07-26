<?php

namespace App\Http\Controllers\Api\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConversationRequests\FormRequestConversation;
use App\Services\Conversation\ConversationService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ConversationController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected ConversationService $_conversationService
    ) {}

    // ─────────────────────────────────────────────────────────
    /**
     * POST /api/chat/open
     *
     * الغيست يفتح محادثة مع هوست معين (أو يجلب الموجودة).
     * Body: { "host_user_id": 5 }
     */
    public function open(FormRequestConversation $request): JsonResponse
    {
        try {
            $result = $this->_conversationService->openOrCreate(
                $request->user()->id,
                $request->validated()['host_user_id']
            );

            if (isset($result['error'])) {
                return $this->Error([], $result['error'], 422);
            }

            return $this->Success($result, 'Conversation opened successfully.', 201);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * GET /api/chat/list
     *
     * كل محادثات المستخدم (guest أو host) مرتبة بآخر رسالة.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $result = $this->_conversationService->list($request->user()->id);

            return $this->Success($result, 'Conversations retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * GET /api/chat/show?conversation_id=1
     *
     * محادثة واحدة بمعرّفها مع تفاصيل الطرف الآخر.
     */
    public function show(FormRequestConversation $request): JsonResponse
    {
        try {
            $result = $this->_conversationService->show(
                $request->validated()['conversation_id'],
                $request->user()->id
            );

            if (empty($result)) {
                return $this->Error([], 'Conversation not found.', 404);
            }

            return $this->Success($result, 'Conversation retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * POST /api/chat/send
     *
     * إرسال رسالة + broadcast Pusher للطرف الآخر.
     * Body: { "conversation_id": 1, "body": "مرحبا" }
     *
     * يعمل للغيست والهوست على حدٍّ سواء.
     */
    public function send(FormRequestConversation $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->_conversationService->send(
                $data['conversation_id'],
                $request->user()->id,
                $data['body']
            );

            if (isset($result['error'])) {
                return $this->Error([], $result['error'], 403);
            }

            return $this->Success($result, 'Message sent successfully.', 201);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * GET /api/chat/messages?conversation_id=1&per_page=30
     *
     * عرض رسائل محادثة مع pagination (الأحدث أولاً).
     * يتضمن is_read لكل رسالة للتمييز في الفرونت.
     */
    public function messages(FormRequestConversation $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->_conversationService->getMessages(
                $data['conversation_id'],
                $request->user()->id,
                $data['per_page'] ?? 30
            );

            if (isset($result['error'])) {
                return $this->Error([], $result['error'], 403);
            }

            return $this->Success($result, 'Messages retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * POST /api/chat/read
     *
     * تحديد كل الرسائل غير المقروءة في المحادثة كمقروءة
     * + broadcast Pusher لإعلام المُرسِل.
     * Body: { "conversation_id": 1 }
     */
    public function markAsRead(FormRequestConversation $request): JsonResponse
    {
        try {
            $result = $this->_conversationService->markAsRead(
                $request->validated()['conversation_id'],
                $request->user()->id
            );

            if (isset($result['error'])) {
                return $this->Error([], $result['error'], 403);
            }

            return $this->Success($result, 'Messages marked as read.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    /**
     * GET /api/chat/unread
     *
     * إجمالي عدد الرسائل غير المقروءة عبر كل المحادثات.
     * يُستخدَم لعرض الـ badge في الـ navbar.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $result = $this->_conversationService->totalUnreadCount($request->user()->id);

            return $this->Success($result, 'Unread count retrieved.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }
}
