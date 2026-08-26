<?php
// app/Http/Requests/BookingRequests/FormRequestGuestBooking.php

namespace App\Http\Requests\BookingRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestHostBooking extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'index'          => $this->indexRules(),
            'show'           => $this->showRules(),
            default          => [],
        };
    }

    private function showRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ];
    }


    private function indexRules(): array
    {
        return [
            'status'   => ['nullable', 'in:pending,confirmed,active,completed,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
