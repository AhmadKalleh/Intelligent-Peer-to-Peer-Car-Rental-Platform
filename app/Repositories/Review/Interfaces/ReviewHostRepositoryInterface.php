<?php
// app/Repositories/Review/Interfaces/ReviewHostRepositoryInterface.php

namespace App\Repositories\Review\Interfaces;

interface ReviewHostRepositoryInterface
{
    /**
     * يحسب متوسط تقييم الهوست من 5 مع عدد التقييمات.
     */
    public function averageRating(int $hostId): array;
}
