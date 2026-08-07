<?php
// app/Http/Requests/ReviewRequests/FormRequestGuestReview.php

namespace App\Http\Requests\ReviewRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestReview extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'submit' => $this->submitRules(),
            default  => [],
        };
    }

    private function submitRules(): array
    {
        return [
            'booking_id'            => ['required', 'integer', 'exists:bookings,id'],
            'overall_rating'        => ['required', 'numeric', 'min:1', 'max:5'],
            'comment'               => ['nullable', 'string', 'max:1000'],
            'cleanliness_rating'    => ['nullable', 'numeric', 'min:1', 'max:5'],
            'maintenance_rating'    => ['nullable', 'numeric', 'min:1', 'max:5'],
            'comfort_rating'        => ['nullable', 'numeric', 'min:1', 'max:5'],
            'communication_rating'  => ['nullable', 'numeric', 'min:1', 'max:5'],
            'punctuality_rating'    => ['nullable', 'numeric', 'min:1', 'max:5'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
