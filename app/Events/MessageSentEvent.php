<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُبثّ على القناة الخاصة بالمحادثة عند إرسال رسالة جديدة.
 *
 * القناة: private-conversation.{conversationId}
 * يستمع إليها الطرفان (guest + host) المشتركان في المحادثة.
 *
 * في الفرونت (Pusher JS):
 *   channel.bind('message.sent', (data) => { ... });
 */
class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message      $message,
        public Conversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'body'            => $this->message->body,
            'is_read'         => $this->message->is_read,
            'created_at'      => $this->message->created_at->toIso8601String(),
            // العدادات المحدّثة لكلا الطرفين
            'guest_unread_count' => $this->conversation->guest_unread_count,
            'host_unread_count'  => $this->conversation->host_unread_count,
        ];
    }
}
