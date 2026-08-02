<?php

namespace App\Http\Requests\AiChatRequests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestAiChat extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        if (! $this->user()->hasRole('guest')) {
            throw new HttpResponseException(
                $this->Error([], 'Unauthorized. AI chat is only available for guests.', 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'send' => [
                'content' => ['required', 'string', 'max:2000'],
            ],
            'index' => [
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
                'page'     => ['nullable', 'integer', 'min:1'],
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
