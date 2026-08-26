<?php
// app/Http/Requests/HandoverRequests/FormRequestGuestHandover.php

namespace App\Http\Requests\HandoverRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestHandover extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'confirm' => $this->confirmRules(),
            default   => [],
        };
    }

    private function confirmRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'code'       => ['required', 'string', 'min:4', 'max:20'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
