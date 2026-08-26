<?php

// app/Events/AiMessageSentEvent.php

namespace App\Events;

use App\Models\Message2;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُبَث فوراً عند إنشاء أي رسالة (من الضيف أو من الـ AI) داخل محادثة
 * الضيف مع المساعد الذكي، على قناة خاصة بذلك الضيف فقط.
 */
class AiMessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public Message2 $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ai-chat.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ai-chat.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender'          => $this->message->sender,
            'content'         => $this->message->content,
            'is_read'         => (bool) $this->message->is_read,
            'created_at'      => $this->message->created_at?->toIso8601String(),
        ];
    }
}
