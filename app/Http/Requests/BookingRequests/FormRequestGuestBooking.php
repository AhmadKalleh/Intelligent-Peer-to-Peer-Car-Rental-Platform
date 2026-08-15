<?php
// app/Http/Requests/BookingRequests/FormRequestGuestBooking.php

namespace App\Http\Requests\BookingRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestBooking extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'calculatePrice' => $this->calculatePriceRules(),
            'createBooking'  => $this->createBookingRules(),
            'cancelBooking'  => $this->cancelBookingRules(),
            'index'          => $this->indexRules(),
            'show'           => $this->showRules(),
            'checkPayment'   => [],
            default          => [],
        };
    }

    private function calculatePriceRules(): array
    {
        return [
            'vehicle_id'       => ['required', 'integer', 'exists:vehicles,id'],
            'start_date'       => ['required', 'date', 'after_or_equal:today'],
            'end_date'         => ['required', 'date', 'after:start_date'],
            'delivery_type'    => ['required', 'in:pickup,delivery'],
            'coupon_code'      => ['nullable', 'string', 'max:50'],
        ];
    }

    private function createBookingRules(): array
    {
        return [
            'vehicle_id'       => ['required', 'integer', 'exists:vehicles,id'],
            'start_date'       => ['required', 'date', 'after_or_equal:today'],
            'end_date'         => ['required', 'date', 'after:start_date'],
            'total_days'         => ['required', 'integer', 'min:1'],
            'price_per_day'      => ['required', 'numeric', 'min:0'],
            'subtotal'           => ['required', 'numeric', 'min:0'],
            'discount_amount'    => ['required', 'numeric', 'min:0'],
            'delivery_fee'       => ['required', 'numeric', 'min:0'],
            'platform_fee'       => ['required', 'numeric', 'min:0'],
            'total_amount'       => ['required', 'numeric', 'min:0'],
            'coupon_code'      => ['nullable', 'string', 'max:50'],
        ];
    }

    private function cancelBookingRules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'min:5', 'max:500'],
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
