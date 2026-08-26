<?php
// app/Http/Controllers/Api/Review/HostReviewController.php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Services\Review\ReviewHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostReviewController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected ReviewHostService $_reviewHostService
    ) {}

    // ─── GET /api/host/reviews/rating ──────────────────────────
    // بيرجع متوسط تقييم الهوست الحالي من 5 + عدد التقييمات
    public function averageRating(): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_reviewHostService->averageRating($hostId);
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
