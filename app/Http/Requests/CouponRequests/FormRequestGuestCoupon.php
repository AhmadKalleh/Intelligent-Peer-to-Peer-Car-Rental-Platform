<?php
// app/Http/Requests/CouponRequests/FormRequestGuestCoupon.php

namespace App\Http\Requests\CouponRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;

class FormRequestGuestCoupon extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'validateCoupon' => $this->validateRules(),
            default    => [],
        };
    }

    private function validateRules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50'],
            'subtotal'    => ['required', 'numeric', 'min:0'],
            'total_days'  => ['required', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
