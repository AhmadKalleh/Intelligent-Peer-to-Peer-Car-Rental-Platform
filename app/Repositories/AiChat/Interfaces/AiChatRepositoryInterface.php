<?php

namespace App\Repositories\AiChat\Interfaces;

use App\Models\Conversation2;
use App\Models\Message2;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AiChatRepositoryInterface
{
    /**
     * يرجع محادثة الضيف مع المساعد الذكي، وينشئها إذا لم تكن موجودة.
     */
    public function getOrCreateConversation(int $userId): Conversation2;

    public function createMessage(int $conversationId, string $sender, string $content, bool $isRead = false): Message2;

    public function touchConversation(int $conversationId): void;

    public function paginateMessages(int $conversationId, int $perPage = 30, int $page = 1): LengthAwarePaginator;

    /**
     * آخر N رسالة بترتيب زمني تصاعدي، تُستخدم كسياق يُرسل للنموذج.
     */
    public function getRecentHistory(int $conversationId, int $limit = 20): Collection;

    public function unreadCount(int $conversationId): int;

    public function markAllAsRead(int $conversationId): int;
}
