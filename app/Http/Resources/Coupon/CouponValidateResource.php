<?php
// app/Http/Resources/Coupon/CouponValidateResource.php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponValidateResource extends JsonResource
{
    private float $subtotal;

    public function __construct($resource, float $subtotal)
    {
        parent::__construct($resource);
        $this->subtotal = $subtotal;
    }

    public function toArray(Request $request): array
    {
        $discountAmount = $this->resource->calculateDiscount($this->subtotal);

        return [
            'code'             => $this->code,
            'discount_type'    => $this->discount_type,
            'discount_value'   => (float) $this->discount_value,
            'discount_amount'  => $discountAmount,
            'subtotal'         => $this->subtotal,
            'total_after_discount' => round($this->subtotal - $discountAmount, 2),
        ];
    }
}
