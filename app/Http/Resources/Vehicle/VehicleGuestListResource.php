<?php
// app/Http/Resources/VehicleResource.php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleGuestListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // primaryImage هي علاقة ORM الآن وليست array
        $primaryImage = $this->whenLoaded('primaryImage');

        return [
            'id'                 => $this->id,
            'make'               => $this->make,
            'model'              => $this->model,
            'year'               => $this->year,
            'city'               => $this->city,
            'base_price_per_day' => (float) ($this->current_price ?? $this->base_price_per_day),
            'rating_avg'         => (float) ($this->rating_avg ?? 0.0),
            'total_bookings'     => (int) $this->total_bookings,
            // صورة بسيطة للقائمة
            'primary_image'      => $primaryImage
                                        ? url(Storage::url($primaryImage->path))
                                        : null,
            'listing_status'    => $this->listing_status,

            // distance موجود فقط في Nearby
            'distance_km'        => isset($this->distance)
                                        ? round((float) $this->distance, 2)
                                        : null,
        ];
    }
}
