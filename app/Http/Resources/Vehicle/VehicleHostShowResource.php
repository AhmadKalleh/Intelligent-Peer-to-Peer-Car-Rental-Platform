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
            'availability_info' => $this->buildAvailabilityInfo(),

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

    private function buildAvailabilityInfo(): array
    {
        $availabilities = $this->whenLoaded('availabilities', fn() =>
            $this->availabilities
        );

        if (!$availabilities || $availabilities->isEmpty()) {
            return [
                'status'        => 'no_availability',
                'message'       => 'No availability set for this vehicle.',
                'action'        => 'Please set an availability period for your vehicle.',
                'availabilities'=> [],
            ];
        }

        // ── جلب سجل الإتاحة الأساسي (type=available) ─────────
        $mainAvailability = $availabilities->firstWhere('type', 'available');

        // ── جلب سجلات الحجب ──────────────────────────────────
        $snoozeRecords  = $availabilities->where('type', 'snoozed');
        $bookingRecords = $availabilities->where('type', 'booking_block');

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // تحديد حالة الإتاحة الرئيسية
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        $mainStatus = $this->resolveMainAvailabilityStatus($mainAvailability);

        return [
            // ─── الحالة العامة ────────────────────────────────
            'status'  => $mainStatus['status'],
            'message' => $mainStatus['message'],
            'action'  => $mainStatus['action'],

            // ─── الإتاحة الرئيسية ─────────────────────────────
            'main_availability' => $mainAvailability ? [
                'id'             => $mainAvailability->id,
                'available_from' => $mainAvailability->available_from->toDateString(),
                'available_to'   => $mainAvailability->available_to->toDateString(),
                'is_blocked'     => (bool) $mainAvailability->is_blocked,
                'blocked_by'     => $mainAvailability->blocked_by,
                'block_reason'   => $mainAvailability->block_reason,
            ] : null,

            // ─── فترات الـ Snooze ─────────────────────────────
            'snooze_periods' => $snoozeRecords->map(fn($s) => [
                'id'             => $s->id,
                'available_from' => $s->available_from->toDateString(),
                'available_to'   => $s->available_to->toDateString(),
                'block_reason'   => $s->block_reason,
                'status'         => match(true) {
                    now()->between($s->available_from, $s->available_to) => 'active',
                    now()->lt($s->available_from)                        => 'upcoming',
                    default                                               => 'expired',
                },
            ])->values(),

            // ─── فترات الحجز ──────────────────────────────────
            'booking_blocks' => $bookingRecords->map(fn($b) => [
                'id'             => $b->id,
                'available_from' => $b->available_from->toDateString(),
                'available_to'   => $b->available_to->toDateString(),
                'block_reason'   => $b->block_reason,
            ])->values(),
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Helper: تحديد حالة الإتاحة الرئيسية
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function resolveMainAvailabilityStatus($mainAvailability): array
    {
        if (!$mainAvailability) {
            return [
                'status'  => 'no_availability',
                'message' => 'No availability period set.',
                'action'  => 'Please add an availability period to list your vehicle.',
            ];
        }

        // ── محجوب من النظام ───────────────────────────────────
        if ($mainAvailability->is_blocked && $mainAvailability->blocked_by === 'system') {
            return [
                'status'  => 'expired_by_system',
                'message' => 'Your availability period has expired. Your vehicle has been unlisted automatically.',
                'action'  => 'Please update your availability period to relist your vehicle.',
            ];
        }

        // ── محجوب من الهوست ──────────────────────────────────
        if ($mainAvailability->is_blocked && $mainAvailability->blocked_by === 'host') {
            return [
                'status'  => 'blocked_by_host',
                'message' => 'You have manually blocked this vehicle.',
                'action'  => 'You can unblock your vehicle by updating the availability.',
            ];
        }

        // ── منتهي التاريخ لكن لم يُحجب بعد ──────────────────
        if ($mainAvailability->available_to->lt(now())) {
            return [
                'status'  => 'expired',
                'message' => 'Your availability period has ended.',
                'action'  => 'Please update your availability period.',
            ];
        }

        // ── نشط ───────────────────────────────────────────────
        return [
            'status'  => 'active',
            'message' => 'Your vehicle is currently available for booking.',
            'action'  => null,
        ];
    }
}
