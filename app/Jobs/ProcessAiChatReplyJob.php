<?php

namespace App\Jobs;

use App\Events\AiMessageSentEvent;
use App\Models\Message2;
use App\Repositories\AiChat\Interfaces\AiChatRepositoryInterface;
use App\Services\AiChat\AiChatAssistant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * يعالج رد المساعد الذكي بشكل غير متزامن (Queue) حتى يبقى إرسال
 * رسالة الضيف سريعاً جداً (Real-time)، ثم يُبَث رد الـ AI فور جاهزيته
 * عبر Pusher دون الحاجة لانتظار المستخدم للرد.
 */
class ProcessAiChatReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 45;

    public function __construct(
        public int $conversationId,
        public int $userId,
    ) {}

    public function handle(AiChatRepositoryInterface $chatRepository, AiChatAssistant $assistant): void
    {
        $history = $chatRepository->getRecentHistory($this->conversationId);

        try {
            $replyContent = $assistant->reply($history);
       } catch (Throwable $e) {
    \Illuminate\Support\Facades\Log::error('AI Chat reply failed', [
        'conversation_id' => $this->conversationId,
        'message'         => $e->getMessage(),
        'file'            => $e->getFile(),
        'line'            => $e->getLine(),
        'trace'           => $e->getTraceAsString(),
    ]);
    $replyContent = 'عذراً، حدثت مشكلة تقنية غير متوقعة. حاول مرة أخرى بعد قليل 🙏';
}

        $aiMessage = $chatRepository->createMessage(
            $this->conversationId,
            Message2::SENDER_AI,
            $replyContent
        );

        $chatRepository->touchConversation($this->conversationId);

        broadcast(new AiMessageSentEvent($this->userId, $aiMessage));
    }
}
