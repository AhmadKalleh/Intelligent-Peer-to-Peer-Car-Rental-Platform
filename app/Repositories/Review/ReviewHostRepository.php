<?php
// app/Repositories/Review/ReviewHostRepository.php

namespace App\Repositories\Review;

use App\Models\Review;
use App\Repositories\Review\Interfaces\ReviewHostRepositoryInterface;

class ReviewHostRepository implements ReviewHostRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // AVERAGE RATING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function averageRating(int $hostId): array
    {
        $stats = Review::where('host_id', $hostId)
            ->where('is_visible', true)
            ->selectRaw('AVG(overall_rating) as average, COUNT(*) as total')
            ->first();

        return [
            'average' => $stats->average ? round((float) $stats->average, 1) : 0.0,
            'total'   => (int) $stats->total,
        ];
    }
}
