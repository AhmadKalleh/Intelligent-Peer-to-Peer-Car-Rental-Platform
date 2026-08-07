<?php
// app/Http/Controllers/Api/Review/GuestReviewController.php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequests\FormRequestGuestReview;
use App\Services\Review\ReviewGuestService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestReviewController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected ReviewGuestService $_reviewGuestService
    ) {}

    // ─── POST /api/Guest/reviews ───────────────────────────────
    public function submit(FormRequestGuestReview $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_reviewGuestService->submit($request->validated(), auth()->id());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
