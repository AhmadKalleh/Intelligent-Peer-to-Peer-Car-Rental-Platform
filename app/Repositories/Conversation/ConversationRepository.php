<?php

namespace App\Repositories\Conversation;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Conversation\Interfaces\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ConversationRepository implements ConversationRepositoryInterface
{
    // ─── Conversations ───────────────────────────────────────

    public function findOrCreate(int $guestUserId, int $hostUserId): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'guest_user_id' => $guestUserId,
                'host_user_id'  => $hostUserId,
            ],
            [
                'guest_unread_count' => 0,
                'host_unread_count'  => 0,
            ]
        );
    }

    public function listForUser(int $userId): Collection
    {
        return Conversation::query()
            ->where('guest_user_id', $userId)
            ->orWhere('host_user_id', $userId)
            ->with([
                // بيانات الغيست (اسم + صورة)
                'guestUser' => fn($q) => $q->select('id', 'full_name')
                                           ->with('image:id,imageable_id,imageable_type,path'),
                // بيانات الهوست
                'hostUser'  => fn($q) => $q->select('id', 'full_name')
                                           ->with('image:id,imageable_id,imageable_type,path'),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUser(int $conversationId, int $userId): ?Conversation
    {
        return Conversation::query()
            ->where('id', $conversationId)
            ->where(function ($q) use ($userId) {
                $q->where('guest_user_id', $userId)
                  ->orWhere('host_user_id',  $userId);
            })
            ->with([
                'guestUser' => fn($q) => $q->select('id', 'full_name')
                                           ->with('image:id,imageable_id,imageable_type,path'),
                'hostUser'  => fn($q) => $q->select('id', 'full_name')
                                           ->with('image:id,imageable_id,imageable_type,path'),
            ])
            ->first();
    }

    // ─── Messages ────────────────────────────────────────────

    public function sendMessage(Conversation $conversation, int $senderUserId, string $body): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $senderUserId,
            'body'            => $body,
            'is_read'         => false,
        ]);

        // تحديث آخر رسالة + عداد الطرف المستقبِل
        $conversation->touchLastMessage($body, $senderUserId);

        return $message->fresh();
    }

    public function getMessages(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->with('sender:id,full_name')
            ->orderByDesc('created_at')      // الأحدث أولاً → الفرونت يعكس
            ->paginate($perPage);
    }

    public function markAsRead(Conversation $conversation, int $readerUserId): int
    {
        // نحدّد الرسائل المُرسَلة من الطرف الآخر فقط (الغير مقروءة للقارئ)
        $updated = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $readerUserId)   // أُرسلت من الطرف الآخر
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($updated > 0) {
            $conversation->resetUnreadCount($readerUserId);
        }

        return $updated;
    }

    // ─── Unread Counts ───────────────────────────────────────

    public function totalUnreadCount(int $userId): int
    {
        $asGuest = Conversation::where('guest_user_id', $userId)
                               ->sum('guest_unread_count');

        $asHost  = Conversation::where('host_user_id', $userId)
                               ->sum('host_unread_count');

        return (int) ($asGuest + $asHost);
    }
}
