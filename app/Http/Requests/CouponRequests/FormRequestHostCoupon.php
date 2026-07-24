<?php
// app/Http/Requests/CouponRequests/FormRequestHostCoupon.php

namespace App\Http\Requests\CouponRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Validation\Rule;

class FormRequestHostCoupon extends FormRequest
{
    use ResponseHelper;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'index'        => [],
            'store'        => $this->storeRules(),
            'update'       => $this->updateRules(),
            'toggleStatus' => $this->requiredid(),
            'destroy'      => $this->requiredid(),
            'showUses'     => $this->requiredid(),
            default        => [],
        };
    }

    private function requiredid(): array
    {
        return [
            'coupon_id' => ['required', 'integer', 'exists:coupons,id'],
        ];
    }
    private function storeRules(): array
    {
        return [
            'code'             => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'discount_type'    => ['required', 'in:percentage,fixed'],
            'discount_value'   => [
                'required',
                'numeric',
                'min:0.01',
                // إذا percentage لا يتجاوز 100
                $this->input('discount_type') === 'percentage'
                    ? 'max:100'
                    : 'max:999999',
            ],
            'min_booking_days' => ['nullable', 'integer', 'min:1'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'valid_from'       => ['nullable', 'date','after_or_equal:today'],
            'valid_until'      => ['nullable', 'date', 'after:valid_from'],
        ];
    }

    private function updateRules(): array
    {
        $coupon_id = $this->input('coupon_id');

        return [
            'coupon_id'         => ['required', 'integer', 'exists:coupons,id'],
            'code'             => ['sometimes', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon_id)],
            'discount_type'    => ['sometimes', 'in:percentage,fixed'],
            'discount_value'   => ['sometimes', 'numeric', 'min:0.01'],
            'min_booking_days' => ['nullable', 'integer', 'min:1'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'valid_from'       => ['nullable', 'date','after_or_equal:today'],
            'valid_until'      => ['nullable', 'date', 'after:valid_from'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->Validation([], $validator->errors()->first(), 422)
        );
    }
}
