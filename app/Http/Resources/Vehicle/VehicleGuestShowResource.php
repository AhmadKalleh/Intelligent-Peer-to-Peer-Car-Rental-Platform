<?php
// app/Http/Resources/VehicleShowResource.php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleGuestShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الأولى: تفاصيل السيارة الكاملة
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'details' => [
                'id'                  => $this->id,
                'make'                => $this->make,
                'model'               => $this->model,
                'year'                => $this->year,
                'color'               => $this->color,
                'fuel_type'           => $this->fuel_type,
                'transmission'        => $this->transmission,
                'engine_capacity'     => $this->engine_capacity,
                'seats'               => $this->seats,
                'city'                => $this->city,
                'pickup_address'      => $this->pickup_address,
                'pickup_lat'          => $this->pickup_lat,
                'pickup_lng'          => $this->pickup_lng,
                'guest_instructions'  => $this->guest_instructions,
                'listing_status'      => $this->listing_status,
                'base_price_per_day' => (float) ($this->current_price ?? $this->base_price_per_day),
                'delivery_available'  => (bool) $this->delivery_available,
                'delivery_fee'        => $this->delivery_fee ? (float) $this->delivery_fee : null,
                'total_bookings'      => (int) $this->total_bookings,
                'total_reviews'       => (int) $this->total_reviews,
                'rating_avg'          => (float) ($this->rating_avg ?? 0.0),

                'images'              => $this->whenLoaded('images', fn() =>
                    $this->images
                    ->where('type', 'vehicle_image')
                    ->map(fn($img) => [
                        'id'         => $img->id,
                        'url'        => url(Storage::url($img->path))??null,
                        'is_primary' => (bool) $img->is_primary,
                        'sort_order' => $img->sort_order,
                    ])->values()
                ),

                'availabilities' => $this->whenLoaded('availabilities', fn() =>
                    $this->availabilities->map(fn($a) => [
                        'available_from' => $a->available_from->toDateString(),
                        'available_to'   => $a->available_to->toDateString(),
                    ])
                ),

                'custom_pricings'     => $this->whenLoaded('customPricings', fn() =>
                    $this->customPricings->map(fn($p) => [
                        'date_from'     => $p->date_from,
                        'date_to'       => $p->date_to,
                        'price_per_day' => (float) $p->price_per_day,
                        'reason'        => $p->reason,
                    ])
                ),
            ],

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الثانية: Hosted By
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'hosted_by' => $this->whenLoaded('host', fn() => [
                'name'            => $this->host->user->full_name ?? null,
                'is_all_star_host'=> (bool) ($this->is_all_star_host ?? false),
                'rating_avg'      => (float) ($this->host->rating_avg ?? 0.0),
                'total_trips'     => (int) $this->host->total_trips,
                'member_since' => $this->host->created_at
                    ? 'Joined ' . $this->host->created_at->format('M Y')
                    : null,

                'image' =>$this->host->user->image
                        ? url(Storage::url($this->host->user->image->path))
                        : url(Storage::url('users/profile-user.png')),

                ]),

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // الفقرة الثالثة: Features + Reviews
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            'vehicle_features' => $this->whenLoaded('features', fn() =>
                $this->features->map(fn($f) => [
                    'name' => $f->name,
                ])
            ),

            'ratings_and_reviews' => $this->whenLoaded('reviews', function () {
                $reviews = $this->reviews;

                if ($reviews->isEmpty()) {
                    return [
                        'overall_rating'      => 0.0,
                        'total_ratings'       => 0,
                        'breakdown'      => [
                            'cleanliness'   => 0.0,
                            'maintenance'   => 0.0,
                            'comfort'       => 0.0,
                            'communication' => 0.0,
                            'punctuality'   => 0.0,
                        ],

                    ];
                }

                return [
                    'overall_rating' => (float) ($this->rating_avg ?? 0.0),
                    'total_ratings'   => $reviews->count(),


                    'breakdown'      => [
                        // تقييم السيارة
                        'cleanliness'   => round((float) $reviews->avg('cleanliness_rating'), 1),
                        'maintenance'   => round((float) $reviews->avg('maintenance_rating'), 1),
                        'comfort'       => round((float) $reviews->avg('comfort_rating'), 1),
                        // تقييم الهوست
                        'communication' => round((float) $reviews->avg('communication_rating'), 1),
                        'punctuality'   => round((float) $reviews->avg('punctuality_rating'), 1),
                    ],


                ];
            }),
        ];
    }
}
