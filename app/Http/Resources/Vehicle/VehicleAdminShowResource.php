<?php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleAdminShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ─── تفاصيل السيارة ───────────────────────────────
            'id'                     => $this->id,
            'make'                   => $this->make,
            'model'                  => $this->model,
            'year'                   => $this->year,
            'color'                  => $this->color,
            'fuel_type'              => $this->fuel_type,
            'transmission'           => $this->transmission,
            'engine_capacity'        => $this->engine_capacity,
            'seats'                  => $this->seats,
            'plate_number'           => $this->plate_number,
            'city'                   => $this->city,
            'pickup_address'         => $this->pickup_address,
            'pickup_lat'             => $this->pickup_lat,
            'pickup_lng'             => $this->pickup_lng,
            'base_price_per_day'     => (float) $this->base_price_per_day,
            'delivery_available'     => (bool) $this->delivery_available,
            'delivery_fee'           => $this->delivery_fee ? (float) $this->delivery_fee : null,
            'guest_instructions'     => $this->guest_instructions,
            'listing_status'         => $this->listing_status,
            'submitted_at'           => $this->created_at?->toDateString(),

            // ─── صور السيارة ──────────────────────────────────
            'vehicle_images'         => $this->whenLoaded('images', fn() =>
                $this->images
                ->where('type', 'vehicle_image')
                ->map(fn($img) => [
                    'id'         => $img->id,
                    'url'        => url(Storage::url($img->path)),
                    'is_primary' => (bool) $img->is_primary,
                    'sort_order' => $img->sort_order,
                ])->values()
            ),

            // ─── دفتر الميكانيك (مرتبط بالسيارة) ─────────────
            'mechanic_booklet'       => $this->whenLoaded('mechanicBooklet', fn() =>
                $this->mechanicBooklet
                    ? url(Storage::url($this->mechanicBooklet->path))
                    : null
            ),

            // ─── الميزات ──────────────────────────────────────
            'features'               => $this->whenLoaded('features', fn() =>
                $this->features->map(fn($f) => [
                    'id'   => $f->id,
                    'name' => $f->name,
                ])
            ),

            // ─── معلومات الهوست ───────────────────────────────
            'host' => $this->whenLoaded('host', fn() => [
                'id'                 => $this->host->id,
                'is_verified'        => (bool) $this->host->is_verified,
                'total_trips'        => (int) $this->host->total_trips,
                'rating_avg'         => (float) ($this->host->rating_avg ?? 0.0),
                'delivery_available' => (bool) $this->host->delivery_available,
                'verified_at'        => $this->host->verified_at?->toDateString(),

                // بيانات المستخدم
                'user' => $this->host->relationLoaded('user') ? [
                    'id'     => $this->host->user->id,
                    'name'   => $this->host->user->full_name,
                    'email'  => $this->host->user->email,
                    'status' => $this->host->user->status,
                    'avatar' => $this->host->user->image
                                    ? url(Storage::url($this->host->user->image->path))
                                    : url(Storage::url('users/profile-user.png')),
                ] : null ,

                // ─── شهادة القيادة (مرتبطة بالهوست) ──────────
                'driving_license' => $this->when(
                    $this->host && $this->host->relationLoaded('drivingLicense'),
                    fn() => $this->host->drivingLicense
                        ? url(Storage::url($this->host->drivingLicense->path))
                        : null
                ),
            ]),


        ];
    }
}
