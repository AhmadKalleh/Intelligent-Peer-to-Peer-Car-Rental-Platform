<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُبثّ عند قراءة الرسائل → يُصفِّر عداد الغير مقروء في واجهة المُرسِل.
 *
 * القناة: private-conversation.{conversationId}
 * الحدث:  messages.read
 *
 * في الفرونت:
 *   channel.bind('messages.read', (data) => {
 *     // صفّر عداد القراءة في واجهة المُرسِل
 *   });
 */
class MessagesReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public int          $readerUserId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id'    => $this->conversation->id,
            'reader_user_id'     => $this->readerUserId,
            'guest_unread_count' => $this->conversation->guest_unread_count,
            'host_unread_count'  => $this->conversation->host_unread_count,
        ];
    }
}
