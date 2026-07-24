<?php
// app/Http/Resources/Coupon/CouponUseResource.php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponUseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'discount_applied' => (float) $this->discount_applied,
            'used_at' => $this->used_at?->format('d M Y'),

            'user' => $this->whenLoaded('user', fn() => [
                'id'     => $this->user->id,
                'name'   => $this->user->full_name,
                'email'  => $this->user->email,
            ]),

            'booking' => $this->whenLoaded('booking', fn() => [
                'id'           => $this->booking->id,
                'start_date' => $this->booking->start_date?->format('d M Y'),
                'end_date'   => $this->booking->end_date?->format('d M Y'),
                'total_amount' => (float) $this->booking->total_amount,
                'status'       => $this->booking->status,
            ]),
        ];
    }
}
