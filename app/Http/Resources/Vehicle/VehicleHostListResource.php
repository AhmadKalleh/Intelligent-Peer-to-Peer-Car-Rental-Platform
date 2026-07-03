<?php
// app/Http/Resources/Vehicle/VehicleHostListResource.php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleHostListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'make'                 => $this->make,
            'model'                => $this->model,
            'year'                 => $this->year,
            'city'                 => $this->city,
            'base_price_per_day'   => (float) $this->base_price_per_day,
            'listing_status'       => $this->listing_status,
            'admin_review_status'  => $this->admin_review_status,
            'total_bookings'       => (int) $this->total_bookings,
            'total_reviews'        => (int) $this->total_reviews,
            'rating_avg'           => (float) ($this->rating_avg ?? 0.0),
            'delivery_available'   => (bool) $this->delivery_available,
            'submitted_at'         => 'Submitted '.$this->created_at?->format('M Y'),

            'primary_image'        => $this->whenLoaded('primaryImage', fn() =>
                $this->primaryImage
                    ? url(Storage::url( $this->primaryImage->path))
                    : null
            ),
        ];
    }
}
