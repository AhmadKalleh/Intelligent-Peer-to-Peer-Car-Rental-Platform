<?php

namespace App\Repositories\Complaint;

use App\Models\Complaint;
use App\Repositories\Complaint\Interfaces\ComplaintRepositoryInterface;
use App\Services\Complaint\ComplaintReasonService;
use Illuminate\Support\Facades\DB;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    // ─── Submit Complaint (Host or Guest) ─────────────────────────────────────

    public function submitComplaint(int $userId, array $data): array
    {
        if ($userId === (int) $data['reported_user_id']) {
            return ['status' => 'self_report'];
        }

        $reason = ComplaintReasonService::find((int) $data['reason_id']);

        if (!$reason) {
            return ['status' => 'invalid_reason'];
        }

        return DB::transaction(function () use ($userId, $data, $reason) {

            $complaint = Complaint::create([
                'user_id'          => $userId,
                'reported_user_id' => $data['reported_user_id'],
                'reason_key'       => $reason['key'],
                'reason_subject'   => $reason['subject'],
                'reason_text'      => $reason['text'],
                'details'          => $data['details'] ?? null,
                'status'           => 'pending',
            ]);

            return [
                'status'    => 'created',
                'complaint' => $complaint->fresh(['complainant.image', 'reportedUser.image']),
            ];
        });
    }
}
