<?php
// app/Services/Review/ReviewHostService.php

namespace App\Services\Review;

use App\Repositories\Review\Interfaces\ReviewHostRepositoryInterface;

class ReviewHostService
{
    public function __construct(
        protected ReviewHostRepositoryInterface $_reviewHostRepository,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // AVERAGE RATING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function averageRating(int $hostId): array
    {
        $stats = $this->_reviewHostRepository->averageRating($hostId);

        return [
            'data' => [
                'average_rating' => $stats['average'], // من 5
                'total_reviews'  => $stats['total'],
            ],
            'message' => 'Host rating retrieved successfully.',
            'code'    => 200,
        ];
    }
}
