<?php
// app/Http/Requests/HandoverRequests/FormRequestHostHandover.php

namespace App\Http\Requests\HandoverRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestHostHandover extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'generate' => $this->generateRules(),
            default    => [],
        };
    }

    private function generateRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
