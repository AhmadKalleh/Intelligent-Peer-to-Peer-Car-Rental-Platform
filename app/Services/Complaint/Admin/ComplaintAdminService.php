<?php

namespace App\Services\Complaint\Admin;

use App\Repositories\Complaint\Interfaces\ComplaintAdminRepositoryInterface;

class ComplaintAdminService
{
    public function __construct(
        protected ComplaintAdminRepositoryInterface $_complaintAdminRepository
    ) {}

    // ─── Overview: كل الشكاوي مفرزة ضمن 3 لستات ────────────────────────────────

    public function getComplaintsOverview(int $perPage = 15, int $flagThreshold = 5): array
    {
        $overview = $this->_complaintAdminRepository->getComplaintsOverview($perPage, $flagThreshold);

        return [
            'data'    => $overview,
            'message' => 'تم استرجاع الشكاوي بنجاح.',
            'code'    => 200,
        ];
    }

    // ─── الرد على شكوى ─────────────────────────────────────────────────────────

    public function replyToComplaint(int $complaintId, string $reply, int $adminId): array
    {
        $result = $this->_complaintAdminRepository->replyToComplaint($complaintId, $reply, $adminId);

        return match ($result['status']) {
            'already_answered' => ['data' => [], 'message' => 'تم الرد على هذه الشكوى مسبقاً.', 'code' => 422],
            default             => ['data' => $result['complaint'], 'message' => 'تم إرسال الرد بنجاح.', 'code' => 200],
        };
    }

    // ─── عدد الشكاوي الغير مردود عليها ──────────────────────────────────────────

    public function countUnanswered(): array
    {
        $count = $this->_complaintAdminRepository->countUnanswered();

        return [
            'data'    => ['unanswered_count' => $count],
            'message' => 'تم استرجاع عدد الشكاوي الغير مردود عليها بنجاح.',
            'code'    => 200,
        ];
    }
}
