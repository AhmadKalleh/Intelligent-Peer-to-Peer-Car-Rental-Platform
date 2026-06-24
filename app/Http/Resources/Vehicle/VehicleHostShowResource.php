<?php
// app/Http/Resources/Vehicle/VehicleHostShowResource.php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class VehicleHostShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الأولى: المعلومات الأساسية
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'basic_info' => [
                'id'               => $this->id,
                'make'             => $this->make,
                'model'            => $this->model,
                'year'             => $this->year,
                'color'            => $this->color,
                'fuel_type'        => $this->fuel_type,
                'transmission'     => $this->transmission,
                'engine_capacity'  => $this->engine_capacity,
                'seats'            => $this->seats,
                'plate_number'     => $this->plate_number,
                'guest_instructions' => $this->guest_instructions,
            ],

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الثانية: حالة السيارة
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'status_info' => [
                'listing_status'         => $this->listing_status,
                'admin_review_status'    => $this->admin_review_status,
                'admin_rejection_reason' => $this->admin_rejection_reason,
                'snoozed_until' => $this->snoozed_until
                    ? 'Snoozed Until '.$this->snoozed_until->toDateTimeString()
                    : null,

                'reviewed_at' => $this->reviewed_at
                    ? 'Reviewed at '.$this->reviewed_at->format('M Y')
                    : null,

                'submitted_at' => $this->created_at
                    ? 'Submitted '.$this->created_at->format('M Y')
                    : null,
            ],

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الثالثة: التسعير
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'pricing_info' => $this->buildPricingInfo(),

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الرابعة: الموقع
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'location_info' => [
                'city'           => $this->city,
                'pickup_address' => $this->pickup_address,
                'pickup_lat'     => $this->pickup_lat,
                'pickup_lng'     => $this->pickup_lng,
            ],

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الخامسة: الصور
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'images_info' => [
                'vehicle_images'   => $this->whenLoaded('images', fn() =>
                    $this->images
                    ->where('type', 'vehicle_image')
                    ->map(fn($img) => [
                        'id'         => $img->id,
                        'url'        => url(Storage::url($img->path)),
                        'is_primary' => (bool) $img->is_primary,
                        'sort_order' => $img->sort_order,
                    ])->values()
                ),

                'mechanic_booklet' => $this->whenLoaded('mechanicBooklet', fn() =>
                    $this->mechanicBooklet
                        ? url(Storage::url($this->mechanicBooklet->path))
                        : null
                ),
            ],

            // // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // // الفقرة السادسة: الميزات
            // // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'features_info' => [
                'features' => $this->whenLoaded('features', fn() =>
                    $this->features->map(fn($f) => [
                        'id'   => $f->id,
                        'name' => $f->name,
                    ])
                ),
            ],

            // // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // // الفقرة السابعة: الإتاحة
            // // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'availability_info' => [
                'availabilities' => $this->whenLoaded('availabilities', fn() =>
                    $this->availabilities->map(fn($a) => [
                        'id'             => $a->id,
                        'available_from' => $a->available_from->toDateString(),
                        'available_to'   => $a->available_to->toDateString(),
                        'is_blocked'     => (bool) $a->is_blocked,
                        'block_reason'   => $a->block_reason,
                    ])
                ),
            ],

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الثامنة: الإحصائيات
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'stats_info' => [
                'total_bookings' => (int) $this->total_bookings,
                'total_reviews'  => (int) $this->total_reviews,
                'rating_avg'     => (float) ($this->rating_avg ?? 0.0),
            ],
        ];
    }

    private function buildPricingInfo(): array
    {
        $isCustomActive = (bool) $this->is_custom_price_active;
        $currentPrice   = (float) ($this->current_price ?? $this->base_price_per_day);

        // ─── Custom Pricings مع حالة كل فترة ────────────
        $customPricings = $this->whenLoaded('customPricings', fn() =>
            $this->customPricings->map(function ($p) {
                $from       = Carbon::parse($p->date_from);
                $to         = Carbon::parse($p->date_to);
                $now        = now();
                $isActive   = $now->between($from, $to);
                $isUpcoming = $from->gt($now);

                return [
                    'id'            => $p->id,
                    'date_from'     => $from->toDateString(),
                    'date_to'       => $to->toDateString(),
                    'price_per_day' => (float) $p->price_per_day,
                    'reason'        => $p->reason,
                    'status'        => match(true) {
                        $isActive   => 'active',
                        $isUpcoming => 'upcoming',
                        default     => 'expired',
                    },
                    'is_active'   => $isActive,
                    'is_upcoming' => $isUpcoming,
                ];
            })
        );

        // ─── Insights ────────────────────────────────────
        $insights = $this->whenLoaded('customPricings', function () {
            $now      = now();
            $upcoming = $this->customPricings
                ->filter(fn($p) => Carbon::parse($p->date_from)->gt($now))
                ->sortBy('date_from');

            return [
                'has_active_custom_pricing'     => (bool) $this->customPricings
                    ->first(fn($p) => $now->between(
                        Carbon::parse($p->date_from),
                        Carbon::parse($p->date_to)
                    )),
                'next_price_change_at'          => $upcoming->first()
                    ? Carbon::parse($upcoming->first()->date_from)->toDateString()
                    : null,
                'total_upcoming_custom_periods' => (int) $upcoming->count(),
            ];
        });

        return [
            // ─── السعر الأساسي ────────────────────────────
            'base_price_per_day' => (float) $this->base_price_per_day,

            // ─── السعر الحالي الفعلي ──────────────────────
            'current_price'          => $currentPrice,
            'is_custom_price_active' => $isCustomActive,
            'current_pricing_source' => $isCustomActive ? 'custom_pricing' : 'base_price',
            'host_message'           => $isCustomActive
                ? 'Custom pricing is currently ACTIVE and applied to bookings.'
                : 'Base pricing is currently applied. No active custom pricing.',

            // ─── التوصيل ──────────────────────────────────
            'delivery_available' => (bool) $this->delivery_available,
            'delivery_fee'       => $this->delivery_fee ? (float) $this->delivery_fee : null,

            // ─── الفترات المخصصة ──────────────────────────
            'custom_pricings' => $customPricings,

            // ─── Insights ────────────────────────────────
            'insights' => $insights,
        ];
    }
}
