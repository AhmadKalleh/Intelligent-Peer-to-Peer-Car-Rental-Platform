<?php

namespace App\Repositories\AiChat;

use App\Models\Conversation2;
use App\Models\Message2;
use App\Repositories\AiChat\Interfaces\AiChatRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AiChatRepository implements AiChatRepositoryInterface
{
    public function getOrCreateConversation(int $userId): Conversation2
    {
        return Conversation2::query()->firstOrCreate(['user_id' => $userId]);
    }

    public function createMessage(int $conversationId, string $sender, string $content, bool $isRead = false): Message2
    {
        return Message2::create([
            'conversation_id' => $conversationId,
            'sender'          => $sender,
            'content'         => $content,
            'is_read'         => $isRead,
            'read_at'         => $isRead ? now() : null,
        ]);
    }

    public function touchConversation(int $conversationId): void
    {
        Conversation2::whereKey($conversationId)->update(['last_message_at' => now()]);
    }

    public function paginateMessages(int $conversationId, int $perPage = 30, int $page = 1): LengthAwarePaginator
    {
        return Message2::query()
            ->where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getRecentHistory(int $conversationId, int $limit = 20): Collection
    {
        return Message2::query()
            ->where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function unreadCount(int $conversationId): int
    {
        return Message2::query()
            ->where('conversation_id', $conversationId)
            ->where('sender', Message2::SENDER_AI)
            ->where('is_read', false)
            ->count();
    }

    public function markAllAsRead(int $conversationId): int
    {
        return Message2::query()
            ->where('conversation_id', $conversationId)
            ->where('sender', Message2::SENDER_AI)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
