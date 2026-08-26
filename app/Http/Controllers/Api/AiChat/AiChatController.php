<?php

namespace App\Http\Controllers\Api\AiChat;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequests\FormRequestAiChat;
use App\Services\AiChat\AiChatService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AiChatController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected AiChatService $_aiChatService
    ) {}

    /**
     * POST /api/Guest/ai-chat/send
     * إرسال رسالة من الضيف إلى المساعد الذكي.
     * الاستجابة فورية (تخزين + بث رسالة الضيف)، بينما يتم توليد
     * رد الـ AI في الخلفية ويصل عبر Pusher على قناة ai-chat.{userId}.
     */
    public function send(FormRequestAiChat $request): JsonResponse
    {
        try {
            $result = $this->_aiChatService->sendMessage(
                $request->user()->id,
                $request->validated()['content']
            );

            return $this->Success($result, 'Message sent successfully.', 201);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/Guest/ai-chat/messages
     * جلب رسائل محادثة الضيف مع المساعد الذكي (Pagination).
     */
    public function index(FormRequestAiChat $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->_aiChatService->getMessages(
                $request->user()->id,
                (int) ($data['per_page'] ?? 30),
                (int) ($data['page'] ?? 1)
            );

            return $this->Success($result, 'Messages retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/Guest/ai-chat/unread-count
     * عدد رسائل الـ AI غير المقروءة (Badge).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->_aiChatService->unreadCount($request->user()->id);

            return $this->Success(['unread_count' => $count], 'Unread count retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/Guest/ai-chat/read
     * تعليم كل رسائل الـ AI في محادثة الضيف كمقروءة.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $updated = $this->_aiChatService->markAsRead($request->user()->id);

            return $this->Success(['updated' => $updated], 'Messages marked as read successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }
}
