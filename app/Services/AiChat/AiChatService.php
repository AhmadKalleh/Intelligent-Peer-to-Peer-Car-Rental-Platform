<?php

namespace App\Services\AiChat;

use App\Events\AiMessageSentEvent;
use App\Jobs\ProcessAiChatReplyJob;
use App\Models\Message2;
use App\Repositories\AiChat\Interfaces\AiChatRepositoryInterface;

class AiChatService
{
    public function __construct(
        protected AiChatRepositoryInterface $_aiChatRepository
    ) {}

    /**
     * يخزّن رسالة الضيف، يبثّها فوراً (Real-time)، ثم يجدول معالجة
     * رد المساعد الذكي في الخلفية بدون تأخير استجابة الطلب الحالي.
     */
    public function sendMessage(int $userId, string $content): array
    {
        $conversation = $this->_aiChatRepository->getOrCreateConversation($userId);

        $guestMessage = $this->_aiChatRepository->createMessage(
            $conversation->id,
            Message2::SENDER_GUEST,
            $content,
            true
        );

        $this->_aiChatRepository->touchConversation($conversation->id);

        broadcast(new AiMessageSentEvent($userId, $guestMessage));

        ProcessAiChatReplyJob::dispatch($conversation->id, $userId);

        return [
            'conversation_id' => $conversation->id,
            'message' => $this->formatMessage($guestMessage),
        ];
    }

    public function getMessages(int $userId, int $perPage = 30, int $page = 1): array
    {
        $conversation = $this->_aiChatRepository->getOrCreateConversation($userId);

        $paginator = $this->_aiChatRepository->paginateMessages(
            $conversation->id,
            $perPage,
            $page
        );

        return [
            'conversation_id' => $conversation->id,
            'messages' => collect($paginator->items())
                ->map(fn (Message2 $m) => $this->formatMessage($m))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function unreadCount(int $userId): int
    {
        $conversation = $this->_aiChatRepository->getOrCreateConversation($userId);

        return $this->_aiChatRepository->unreadCount($conversation->id);
    }

    public function markAsRead(int $userId): int
    {
        $conversation = $this->_aiChatRepository->getOrCreateConversation($userId);

        return $this->_aiChatRepository->markAllAsRead($conversation->id);
    }

    protected function formatMessage(Message2 $message): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender,
            'content' => $message->content,
            'is_read' => (bool) $message->is_read,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }
}