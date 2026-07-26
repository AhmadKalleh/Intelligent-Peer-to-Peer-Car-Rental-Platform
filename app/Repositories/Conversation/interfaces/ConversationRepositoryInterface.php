<?php

namespace App\Repositories\Conversation\Interfaces;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface ConversationRepositoryInterface
{
    // ─── Conversations ───────────────────────────────────────

    /**
     * فتح أو إيجاد محادثة بين guest و host.
     * إذا لم تكن موجودة يُنشئها تلقائياً.
     */
    public function findOrCreate(int $guestUserId, int $hostUserId): Conversation;

    /**
     * كل محادثات مستخدم معين (guest أو host)
     * مرتبة بآخر رسالة
     */
    public function listForUser(int $userId): Collection;

    /**
     * جلب محادثة واحدة والتحقق أن المستخدم طرف فيها
     */
    public function findForUser(int $conversationId, int $userId): ?Conversation;

    // ─── Messages ────────────────────────────────────────────

    /**
     * إرسال رسالة وتحديث الـ conversation
     */
    public function sendMessage(Conversation $conversation, int $senderUserId, string $body): Message;

    /**
     * جلب رسائل محادثة مع pagination
     */
    public function getMessages(int $conversationId, int $perPage = 30): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * تحديد الرسائل كمقروءة + تصفير العداد
     */
    public function markAsRead(Conversation $conversation, int $readerUserId): int;

    // ─── Unread Counts ───────────────────────────────────────

    /**
     * إجمالي الرسائل غير المقروءة لمستخدم عبر كل محادثاته
     */
    public function totalUnreadCount(int $userId): int;
}
