<?php
// app/Http/Requests/LocationTrackingRequests/FormRequestGuestLocationTracking.php

namespace App\Http\Requests\LocationTrackingRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestLocationTracking extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'update' => $this->updateRules(),
            'show'   => $this->showRules(),
            default  => [],
        };
    }

    private function updateRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'lat'        => ['required', 'numeric', 'between:-90,90'],
            'lng'        => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    private function showRules(): array
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
