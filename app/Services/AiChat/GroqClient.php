<?php

namespace App\Services\AiChat;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * عميل بسيط لاستدعاء Groq Chat Completions API (متوافق مع OpenAI).
 * Groq مجاني، سريع جداً (Inference منخفض الزمن)، ويدعم tool-calling
 * وهو ما يسمح للمساعد بالبحث الفعلي في قاعدة بيانات السيارات
 * بدل تأليف إجابات غير دقيقة.
 */
class GroqClient
{
    protected Client $http;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.groq.api_key');
        $this->baseUrl = (string) config('services.groq.base_url');
        $this->model   = (string) config('services.groq.model');

        $this->http = new Client([
            'timeout' => (int) config('services.groq.timeout', 30),
        ]);
    }

    /**
     * @param array $messages تاريخ المحادثة بصيغة OpenAI ([role, content, ...])
     * @param array $tools    تعريفات الأدوات المتاحة للنموذج (JSON schema)
     *
     * @return array الرد الخام لأول اختيار (choice) من الـ API
     *
     * @throws GroqClientException عند فشل الاتصال أو غياب مفتاح الـ API
     */
    public function chat(array $messages, array $tools = []): array
    {
        if (empty($this->apiKey)) {
            throw new GroqClientException('GROQ_API_KEY غير مُعرّف في ملف .env');
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.3,
            'max_tokens'  => 700,
        ];

        if (! empty($tools)) {
            $payload['tools']       = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = $this->http->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (! isset($body['choices'][0]['message'])) {
                throw new GroqClientException('استجابة غير متوقعة من مزوّد الذكاء الاصطناعي.');
            }

            return $body['choices'][0]['message'];
        } catch (GuzzleException $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            throw new GroqClientException('تعذر الاتصال بمزوّد الذكاء الاصطناعي حالياً.');
        }
    }
}
