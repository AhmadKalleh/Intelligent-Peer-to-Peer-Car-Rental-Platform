<?php

namespace App\Services\Complaint;

use App\Repositories\Complaint\Interfaces\ComplaintRepositoryInterface;

class ComplaintService
{
    public function __construct(
        protected ComplaintRepositoryInterface $_complaintRepository
    ) {}

    // ─── Submit Complaint (Host or Guest) ─────────────────────────────────────

    public function submitComplaint(int $userId, array $data): array
    {
        $result = $this->_complaintRepository->submitComplaint($userId, $data);

        return match ($result['status']) {
            'self_report'    => ['data' => [], 'message' => 'لا يمكنك تقديم شكوى ضد نفسك.', 'code' => 422],
            'invalid_reason' => ['data' => [], 'message' => 'سبب الشكوى المختار غير صالح.',   'code' => 422],
            default          => ['data' => $result['complaint'], 'message' => 'تم إرسال الشكوى بنجاح.', 'code' => 201],
        };
    }
}
