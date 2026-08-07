<?php
// app/Services/Review/ReviewGuestService.php

namespace App\Services\Review;

use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Review\Interfaces\ReviewGuestRepositoryInterface;
use App\Services\Notification\NotificationService;

class ReviewGuestService
{
    public function __construct(
        protected ReviewGuestRepositoryInterface $_reviewGuestRepository,
        protected NotificationService            $_notificationService,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SUBMIT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function submit(array $data, int $userId): array
    {
        $result = $this->_reviewGuestRepository->submit($data, $userId);

        if ($result['status'] === 'not_completed') {
            return [
                'data'    => [],
                'message' => 'You can only review a booking after the trip has ended.',
                'code'    => 422,
            ];
        }

        if ($result['status'] === 'already_reviewed') {
            return [
                'data'    => [],
                'message' => 'You have already reviewed this booking.',
                'code'    => 422,
            ];
        }

        $review = $result['review'];

        // ── إشعار الهوست بتقييم جديد ───────────────────────
        $this->_notificationService->send(
            userId : $review->host->user_id,
            type   : 'new_review',
            title  : 'New Review',
            body   : 'A guest has left you a new review.',
        );

        return [
            'data'    => new ReviewResource($review),
            'message' => 'Review submitted successfully.',
            'code'    => 201,
        ];
    }
}
