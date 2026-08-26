<?php
// app/Http/Resources/Vehicle/VehicleAdminResource.php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ─── معلومات السيارة الأساسية ────────────────────
            'id'                   => $this->id,
            'make'                 => $this->make,
            'model'                => $this->model,
            'year'                 => $this->year,
            'color'                => $this->color,
            'plate_number'         => $this->plate_number,
            'city'                 => $this->city,
            'pickup_address'       => $this->pickup_address,
            'base_price_per_day'   => (float) $this->base_price_per_day,
            'listing_status'       => $this->listing_status,
            'submitted_at'         => $this->created_at?->toDateString(),

            // ─── صورة السيارة ────────────────────────────────
            'primary_image'        => $this->whenLoaded('primaryImage', fn() =>
                $this->primaryImage
                    ? url(Storage::url($this->primaryImage->path))
                    : null
            ),

            // ─── معلومات الهوست ──────────────────────────────
            'host' => $this->whenLoaded('host', fn() => [
                'id'          => $this->host->id,
                'is_verified' => (bool) $this->host->is_verified,
                'total_trips' => (int) $this->host->total_trips,

                'user' => $this->host->relationLoaded('user') ? [
                    'id'        => $this->host->user->id,
                    'full_name' => $this->host->user->full_name,
                    'email'     => $this->host->user->email,
                    'avatar'    => $this->host->user->image
                        ? url(Storage::url($this->host->user->image->path))
                        : url(Storage::url('users/profile-user.png')),
                ] : null,
            ]),
        ];
    }
}
