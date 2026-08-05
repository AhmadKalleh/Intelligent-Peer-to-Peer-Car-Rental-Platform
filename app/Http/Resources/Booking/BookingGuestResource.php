<?php
// app/Http/Resources/Booking/BookingGuestResource.php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingGuestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ─── معلومات الحجز ────────────────────────────
            'id'                  => $this->id,
            'status'              => $this->status,
            'start_date' => $this->start_date?->format('M d, Y'),
            'end_date'   => $this->end_date?->format('M d, Y'),
            'total_days'          => (int) $this->total_days,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_by'        => $this->cancelled_by,
            'created_at' => $this->created_at?->format('M d, Y'),

            // ─── التسعير ──────────────────────────────────
            'pricing' => [
                'base_price_per_day' => (float) $this->base_price_per_day,
                'subtotal'           => (float) $this->subtotal,
                'discount_amount'    => (float) $this->discount_amount,
                'delivery_fee'       => (float) $this->delivery_fee,
                'platform_fee'       => (float) $this->platform_fee,
                'total_amount'       => (float) $this->total_amount,
            ],

            // ─── التوصيل ──────────────────────────────────
            'delivery' => [
                'type'     => $this->delivery_type,
                'address'  => $this->delivery_address,
                'lat'      => $this->delivery_lat,
                'lng'      => $this->delivery_lng,
            ],

            // ─── السيارة ──────────────────────────────────
            'vehicle' => $this->whenLoaded('vehicle', fn() => [
                'id'    => $this->vehicle->id,
                'make'  => $this->vehicle->make,
                'model' => $this->vehicle->model,
                'year'  => $this->vehicle->year,
            ]),

            // ─── الهوست ───────────────────────────────────
            'host' => $this->whenLoaded('host', fn() => [
                'id'   => $this->host->id,
                'name' => $this->host->user->full_name ?? null,
            ]),

            // ─── الدفع ────────────────────────────────────
            'payment' => $this->whenLoaded('payment', fn() => [
                'status'      => $this->payment->status,
                'paid_at'     => $this->created_at?->format('M d, Y'),
            ]),

            // ─── الكوبون ──────────────────────────────────
            'coupon' => $this->whenLoaded('couponUse', fn() =>
                $this->couponUse ? [
                    'code'             => $this->couponUse->coupon->code ?? null,
                    'discount_applied' => (float) $this->couponUse->discount_applied,
                ] : null
            ),
        ];
    }
}
