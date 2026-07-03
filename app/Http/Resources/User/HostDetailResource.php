<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HostDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ─── Host Info ────────────────────────────────────────────────────
            'id'                  => $this->id,
            'is_verified'         => (bool) $this->is_verified,
            'verified_at'         => $this->verified_at?->toDateString(),
            'total_earnings'      => (float) $this->total_earnings,
            'available_balance'   => (float) $this->available_balance,
            'rating_avg'          => $this->rating_avg ? (float) $this->rating_avg : null,
            'total_trips'         => (int) $this->total_trips,
            'delivery_available'  => (bool) $this->delivery_available,
            'delivery_fee_per_km' => $this->delivery_fee_per_km ? (float) $this->delivery_fee_per_km : null,

            // ─── User Info ────────────────────────────────────────────────────
            'user' => $this->whenLoaded('user', fn() => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'email'     => $this->user->email,
                'status'    => $this->user->status,
                'avatar'    => $this->user->image
                    ? url(Storage::url($this->user->image->path))
                    : url(Storage::url('users/profile-user.png')),
            ]),

            // ─── Driving License ─────────────────────────────────────────────
            'driving_license' => $this->whenLoaded('drivingLicense', fn() =>
                $this->drivingLicense
                    ? url(Storage::url($this->drivingLicense->path))
                    : null
            ),

            // ─── Active Vehicles ─────────────────────────────────────────────
            'vehicles' => $this->whenLoaded('vehicles', fn() =>
                $this->vehicles->map(fn($vehicle) => [
                    'id'            => $vehicle->id,
                    'make'          => $vehicle->make,
                    'model'         => $vehicle->model,
                    'year'          => $vehicle->year,
                    'listing_status'=> $vehicle->listing_status,
                    'primary_image' => $vehicle->primaryImage
                        ? url(Storage::url($vehicle->primaryImage->path))
                        : null,
                ])
            ),
        ];
    }
}
