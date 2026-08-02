<?php

namespace App\Services\AiChat;

use App\Models\Message;
use App\Models\Message2;
use Illuminate\Support\Collection;

/**
 * المساعد الذكي المسؤول فقط عن مواضيع التطبيق (تأجير السيارات).
 * يبني سياق المحادثة، يستدعي Groq، وينفّذ استدعاءات الأدوات (tool calls)
 * عند الحاجة (مثل البحث عن سيارة)، ثم يرجّع الرد النهائي كنص.
 */
class AiChatAssistant
{
    protected const MAX_TOOL_ROUNDS = 3;

    public function __construct(
        protected GroqClient $_groqClient,
        protected VehicleSearchTool $_vehicleSearchTool,
    ) {}

    /**
     * @param Collection<int, Message> $history آخر رسائل المحادثة (الأقدم أولاً)
     */
    public function reply(Collection $history): string
    {
        $messages = $this->buildMessages($history);
        $tools    = [VehicleSearchTool::definition()];

        try {
            for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
                $assistantMessage = $this->_groqClient->chat($messages, $tools);

                $toolCalls = $assistantMessage['tool_calls'] ?? [];

                if (empty($toolCalls)) {
                    $content = trim((string) ($assistantMessage['content'] ?? ''));

                    return $content !== ''
                        ? $content
                        : $this->fallbackMessage();
                }

                // نضيف رسالة الـ assistant (التي تحتوي على طلب استدعاء الأداة) للسياق
                $messages[] = [
                    'role'       => 'assistant',
                    'content'    => $assistantMessage['content'] ?? null,
                    'tool_calls' => $toolCalls,
                ];

                foreach ($toolCalls as $toolCall) {
                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall['id'] ?? null,
                        'content'      => json_encode(
                            $this->executeTool($toolCall),
                            JSON_UNESCAPED_UNICODE
                        ),
                    ];
                }
            }

            return $this->fallbackMessage();
        } catch (GroqClientException $e) {
            return 'عذراً، حدثت مشكلة تقنية مؤقتة أثناء التواصل مع المساعد الذكي. حاول مرة أخرى خلال قليل 🙏';
        }
    }

    protected function executeTool(array $toolCall): array
    {
        $name = $toolCall['function']['name'] ?? null;

        if ($name !== 'search_vehicles') {
            return ['error' => 'أداة غير معروفة.'];
        }

        $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];

        return $this->_vehicleSearchTool->search($arguments);
    }

    protected function fallbackMessage(): string
    {
        return 'عذراً، لم أتمكن من إيجاد إجابة مناسبة الآن. هل يمكنك توضيح ما تبحث عنه بخصوص السيارات؟';
    }

    /**
     * @param Collection<int, Message> $history
     */
    protected function buildMessages(Collection $history): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
        ];

        foreach ($history as $message) {
            $messages[] = [
                'role'    => $message->sender === Message2::SENDER_GUEST ? 'user' : 'assistant',
                'content' => $message->content,
            ];
        }

        return $messages;
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
أنت المساعد الذكي الرسمي داخل تطبيق تأجير سيارات بين الأفراد (Peer-to-Peer Car Rental).
مهمتك الوحيدة هي مساعدة المستخدم (الضيف) في كل ما يتعلق بالتطبيق فقط، وتحديداً:
- إيجاد سيارة مناسبة له بناءً على رغبته (المدينة، السعر، عدد المقاعد، نوع ناقل الحركة، نوع الوقود، توفر التوصيل...).
- شرح كيفية استخدام ميزات التطبيق (الحجز، المفضلة، التوصيل، إلخ) بشكل عام دون اختلاق تفاصيل غير مؤكدة.
- الإجابة بأسلوب ودود، مختصر، وباللغة العربية دائماً ما لم يكتب المستخدم بلغة أخرى.

قواعد صارمة يجب الالتزام بها دائماً:
1. عند أي طلب للبحث عن سيارة أو اقتراحها، يجب عليك استدعاء أداة search_vehicles للحصول على نتائج حقيقية من قاعدة البيانات. لا تختلق أبداً أسماء أو أسعار سيارات غير موجودة في نتائج الأداة.
2. إن لم تُرجع الأداة أي نتائج، أخبر المستخدم بذلك بصدق واقترح عليه توسيع معايير البحث.
3. أي سؤال أو طلب لا علاقة له بتطبيق تأجير السيارات (مثل الأسئلة العامة، البرمجة، الطبخ، السياسة، الترفيه، إلخ) يجب رفضه فوراً والاعتذار بجملة قصيرة، ثم توضيح أنك مخصص فقط لمساعدته داخل تطبيق تأجير السيارات.
4. لا تقدم أبداً نصائح طبية أو قانونية أو مالية أو أي معلومات خارج نطاق التطبيق، حتى لو أصرّ المستخدم.
5. حافظ على الردود مختصرة وواضحة ومناسبة لواجهة محادثة (Chat).
6. أسماء المدن داخل قاعدة البيانات مخزّنة بالإنجليزي (مثل Damascus، Aleppo، Homs، Latakia). عندما يذكر المستخدم اسم مدينة بالعربي، مرّر اسمها الإنجليزي المقابل لمعامل city عند استدعاء أداة search_vehicles، حتى لو استمر ردّك النهائي للمستخدم بالعربي.
7. مهم جداً: مرّر لأداة search_vehicles فقط المعايير التي ذكرها المستخدم كشرط إلزامي واضح (مثل "لازم"، "بشرط"، "ضروري"). أما المعايير التي ذكرها كتفضيل أو أفضلية اختيارية فقط (مثل "يفضل"، "لو أمكن"، "بحبذا"، "أحسن إذا")، فلا ترسلها كفلتر إطلاقاً — اكتفِ بذكرها كملاحظة إضافية في ردّك النهائي عند عرض النتائج. إرسال معيار تفضيلي كفلتر إلزامي قد يحذف سيارات مناسبة بدون داعٍ.
8. الأداة search_vehicles قد تُرجع النتيجة مع الحقل relaxed_filters=true و ignored_filters و note، وهذا يعني أن النظام لم يجد سيارات تطابق كل معاييرك فتجاهل تلقائياً بعض المعايير الثانوية (مثل السعر أو المقاعد أو التوصيل) وأرجع أقرب النتائج المتوفرة فعلياً. في هذه الحالة يجب أن تخبر المستخدم بصدق ووضوح أن هذه النتائج لا تحقق كل شروطه بالكامل، وتذكر أي معيار تم تجاهله (استخدم قيمة note كمرجع).
9. عند عرض نتائج search_vehicles على المستخدم، لا يكفي أبداً ذكر العدد الإجمالي فقط (مثل "وجدت 8 سيارات"). يجب عليك سرد كل سيارة من نتائج الأداة على حدة (أو أهم النتائج إذا كانت كثيرة جداً)، مع ذكر على الأقل: الشركة المصنّعة والموديل (make, model, year)، السعر اليومي (price_per_day)، المدينة (city)، عدد المقاعد (seats)، ونوع ناقل الحركة (transmission). استخدم تنسيق قائمة مرقمة أو نقطية واضحة تناسب واجهة محادثة، ولا تختلق أي تفصيل غير موجود في نتائج الأداة.
PROMPT;
    }
}