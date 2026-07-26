<?php

namespace App\Services\Conversation;

use App\Events\MessageSentEvent;
use App\Events\MessagesReadEvent;
use App\Models\User;
use App\Repositories\Conversation\Interfaces\ConversationRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ConversationService
{
    public function __construct(
        protected ConversationRepositoryInterface $_conversationRepository
    ) {}

    // ─────────────────────────────────────────────────────────
    //  OPEN / LIST
    // ─────────────────────────────────────────────────────────

    /**
     * يفتح محادثة بين الغيست والهوست (أو يجلب الموجودة).
     * يُستخدَم عندما يضغط الغيست على "راسِل الهوست" من صفحة السيارة.
     *
     * @param int $guestUserId   — id المستخدم الغيست
     * @param int $hostUserId    — user_id الخاص بالهوست (وليس hosts.id)
     */
    public function openOrCreate(int $guestUserId, int $hostUserId): array
    {
        // تحقق أن host_user_id هو فعلاً هوست
        $hostUser = User::where('id', $hostUserId)->first();

        if (! $hostUser || ! $hostUser->hasRole('host')) {
            return ['error' => 'The selected user is not a host.'];
        }

        $conversation = $this->_conversationRepository->findOrCreate(
            $guestUserId,
            $hostUserId
        );

        return $this->formatConversation($conversation, $guestUserId);
    }

    /**
     * كل محادثات المستخدم (guest أو host)
     */
    public function list(int $userId): array
    {
        $conversations = $this->_conversationRepository->listForUser($userId);

        return $conversations->map(
            fn($c) => $this->formatConversation($c, $userId)
        )->values()->toArray();
    }

    /**
     * محادثة واحدة بمعرّفها
     */
    public function show(int $conversationId, int $userId): array
    {
        $conversation = $this->_conversationRepository->findForUser($conversationId, $userId);

        if (! $conversation) {
            return [];
        }

        return $this->formatConversation($conversation, $userId);
    }

    // ─────────────────────────────────────────────────────────
    //  SEND MESSAGE
    // ─────────────────────────────────────────────────────────

    /**
     * إرسال رسالة + broadcast عبر Pusher
     */
    public function send(int $conversationId, int $senderUserId, string $body): array
    {
        $conversation = $this->_conversationRepository->findForUser($conversationId, $senderUserId);

        if (! $conversation) {
            return ['error' => 'Conversation not found.'];
        }

        $message = $this->_conversationRepository->sendMessage($conversation, $senderUserId, $body);

        // تحديث الـ conversation بعد الإرسال
        $conversation->refresh();

        // Broadcast على القناة الخاصة بالمحادثة
        broadcast(new MessageSentEvent($message, $conversation))->toOthers();

        return $this->formatMessage($message);
    }

    // ─────────────────────────────────────────────────────────
    //  GET MESSAGES
    // ─────────────────────────────────────────────────────────

    /**
     * جلب رسائل محادثة مع pagination (الأحدث أولاً)
     * يتضمن الرسائل غير المقروءة مميزة
     */
    public function getMessages(int $conversationId, int $userId, int $perPage = 30): array
    {
        // تحقق أن المستخدم طرف في المحادثة
        $conversation = $this->_conversationRepository->findForUser($conversationId, $userId);

        if (! $conversation) {
            return ['error' => 'Conversation not found.'];
        }

        $paginated = $this->_conversationRepository->getMessages($conversationId, $perPage);

        $messages = collect($paginated->items())->map(
            fn($m) => $this->formatMessage($m)
        )->values()->toArray();

        return [
            'conversation_id' => $conversationId,
            'messages'        => $messages,
            'unread_count'    => $this->getMyUnreadCount($conversation, $userId),
            'pagination'      => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  MARK AS READ
    // ─────────────────────────────────────────────────────────

    /**
     * تحديد الرسائل كمقروءة + broadcast للطرف الآخر
     */
    public function markAsRead(int $conversationId, int $readerUserId): array
    {
        $conversation = $this->_conversationRepository->findForUser($conversationId, $readerUserId);

        if (! $conversation) {
            return ['error' => 'Conversation not found.'];
        }

        $count = $this->_conversationRepository->markAsRead($conversation, $readerUserId);

        $conversation->refresh();

        // أعلم الطرف الآخر أن رسائله قُرئت
        broadcast(new MessagesReadEvent($conversation, $readerUserId))->toOthers();

        return [
            'marked_as_read' => $count,
            'unread_count'   => $this->getMyUnreadCount($conversation, $readerUserId),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  TOTAL UNREAD COUNT
    // ─────────────────────────────────────────────────────────

    /**
     * إجمالي الرسائل الغير مقروءة عبر كل المحادثات (للـ badge في الفرونت)
     */
    public function totalUnreadCount(int $userId): array
    {
        return [
            'total_unread' => $this->_conversationRepository->totalUnreadCount($userId),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────

    private function formatConversation($conversation, int $myUserId): array
    {
        $isGuest      = (int) $conversation->guest_user_id === $myUserId;
        $otherUser    = $isGuest ? $conversation->hostUser  : $conversation->guestUser;
        $myUnread     = $isGuest
            ? $conversation->guest_unread_count
            : $conversation->host_unread_count;

        return [
            'id'              => $conversation->id,
            'other_user'      => [
                'id'    => $otherUser?->id,
                'name'  => $otherUser?->full_name,
                'image' => $this->userImage($otherUser),
                'role'  => $isGuest ? 'host' : 'guest',
            ],
            'last_message'    => $conversation->last_message,
            'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            'my_unread_count' => $myUnread,
        ];
    }

    private function formatMessage($message): array
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id'       => $message->sender_id,
            'sender_name'     => $message->sender?->full_name,
            'body'            => $message->body,
            'is_read'         => (bool) $message->is_read,
            'read_at'         => $message->read_at?->toIso8601String(),
            'sent_at'         => $message->created_at->toIso8601String(),
            'is_mine'         => false, // الفرونت يقارن sender_id بـ user_id المحفوظ
        ];
    }

    private function getMyUnreadCount($conversation, int $userId): int
    {
        return (int) ((int) $conversation->guest_user_id === $userId
            ? $conversation->guest_unread_count
            : $conversation->host_unread_count);
    }

    private function userImage(?object $user): ?string
    {
        if (! $user || ! $user->image) {
            return url(Storage::url('users/profile-user.png'));
        }

        return url(Storage::url($user->image->path));
    }
}
