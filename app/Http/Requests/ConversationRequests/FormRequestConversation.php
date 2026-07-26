<?php

namespace App\Http\Requests\ConversationRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestConversation extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {

            // فتح / إيجاد محادثة مع هوست
            'open'        => [
                'host_user_id' => ['required', 'integer', 'exists:users,id'],
            ],

            // إرسال رسالة
            'send'        => [
                'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
                'body'            => ['required', 'string', 'max:2000'],
            ],

            // جلب رسائل محادثة
            'messages'    => [
                'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
                'per_page'        => ['sometimes', 'integer', 'min:5', 'max:100'],
            ],

            // تحديد كمقروءة
            'markAsRead'  => [
                'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            ],

            // محادثة واحدة
            'show'        => [
                'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            ],

            default => [],
        };
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
