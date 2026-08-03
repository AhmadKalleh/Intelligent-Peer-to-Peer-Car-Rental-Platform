<?php
// app/Http/Resources/Booking/BookingHostResource.php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingHostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'start_date' => $this->start_date->format('M d, Y'),
            'end_date'   => $this->end_date->format('M d, Y'),
            'total_days' => (int) $this->total_days,
            'created_at' => $this->created_at?->format('M d, Y'),

            'pricing' => [
                'subtotal'        => (float) $this->subtotal,
                'discount_amount' => (float) $this->discount_amount,
                'delivery_fee'    => (float) $this->delivery_fee,
                'platform_fee'    => (float) $this->platform_fee,
                'total_amount'    => (float) $this->total_amount,
                'net_amount'      => (float) ($this->total_amount - $this->platform_fee),
            ],

            'delivery' => [
                'type'    => $this->delivery_type,
                'address' => $this->delivery_address,
            ],

            // ─── الغيست ───────────────────────────────────
            'guest' => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->full_name,
                'email' => $this->user->email,
            ]),

            // ─── السيارة ──────────────────────────────────
            'vehicle' => $this->whenLoaded('vehicle', fn() => [
                'id'    => $this->vehicle->id,
                'make'  => $this->vehicle->make,
                'model' => $this->vehicle->model,
                'year'  => $this->vehicle->year,
            ]),

            'payment_status' => $this->whenLoaded('payment',
                fn() => $this->payment->status
            ),
        ];
    }
}
