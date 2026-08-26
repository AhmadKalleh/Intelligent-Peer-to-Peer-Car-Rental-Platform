<?php
// app/Http/Resources/Review/ReviewResource.php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'booking_id'      => $this->booking_id,
            'overall_rating'  => (float) $this->overall_rating,
            'comment'         => $this->comment,
            'ratings' => [
                'cleanliness'   => $this->cleanliness_rating   !== null ? (float) $this->cleanliness_rating   : null,
                'maintenance'   => $this->maintenance_rating   !== null ? (float) $this->maintenance_rating   : null,
                'comfort'       => $this->comfort_rating       !== null ? (float) $this->comfort_rating       : null,
                'communication' => $this->communication_rating !== null ? (float) $this->communication_rating : null,
                'punctuality'   => $this->punctuality_rating   !== null ? (float) $this->punctuality_rating   : null,
            ],
            'created_at' => $this->created_at?->format('M d, Y'),
        ];
    }
}
