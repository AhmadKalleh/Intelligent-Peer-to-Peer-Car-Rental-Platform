<?php

namespace App\Repositories\Complaint\Admin;

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Complaint\Interfaces\ComplaintAdminRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComplaintAdminRepository implements ComplaintAdminRepositoryInterface
{
    // ─── Overview: كل الشكاوي مفرزة ضمن 3 لستات ────────────────────────────────

    public function getComplaintsOverview(int $perPage = 15, int $flagThreshold = 5): array
    {
        return [
            'unanswered'    => $this->getUnanswered($perPage),
            'answered'      => $this->getAnswered($perPage),
            'flagged_users' => $this->getFlaggedUsers($flagThreshold),
        ];
    }

    // ─── لستا الشكاوي الغير مردود عليها ────────────────────────────────────────

    public function getUnanswered(int $perPage = 15): LengthAwarePaginator
    {
        return Complaint::query()
            ->with(['complainant.image', 'reportedUser.image'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    // ─── لستا الشكاوي المردود عليها (مع الرد) ──────────────────────────────────

    public function getAnswered(int $perPage = 15): LengthAwarePaginator
    {
        return Complaint::query()
            ->with(['complainant.image', 'reportedUser.image', 'repliedByAdmin'])
            ->where('status', 'answered')
            ->orderByDesc('replied_at')
            ->paginate($perPage);
    }

    // ─── لستا المستخدمين (Host/Guest) المشتكى عليهم أكثر من X مرات ────────────

    public function getFlaggedUsers(int $threshold = 5): Collection
    {
        $grouped = Complaint::query()
            ->select('reported_user_id', DB::raw('COUNT(*) as complaints_count'))
            ->groupBy('reported_user_id')
            ->having('complaints_count', '>', $threshold)
            ->orderByDesc('complaints_count')
            ->get();

        if ($grouped->isEmpty()) {
            return collect();
        }

        $users = User::query()
            ->with('image')
            ->whereIn('id', $grouped->pluck('reported_user_id'))
            ->get()
            ->keyBy('id');

        return $grouped->map(function ($row) use ($users) {
            return [
                'user'             => $users->get($row->reported_user_id),
                'complaints_count' => (int) $row->complaints_count,
            ];
        })->values();
    }

    // ─── الرد على شكوى ─────────────────────────────────────────────────────────

    public function replyToComplaint(int $complaintId, string $reply, int $adminId): array
    {
        return DB::transaction(function () use ($complaintId, $reply, $adminId) {

            $complaint = Complaint::lockForUpdate()->findOrFail($complaintId);

            if ($complaint->status === 'answered') {
                return ['status' => 'already_answered'];
            }

            $complaint->update([
                'admin_reply' => $reply,
                'status'      => 'answered',
                'replied_by'  => $adminId,
                'replied_at'  => now(),
            ]);

            Notification::create([
                'user_id' => $complaint->user_id,
                'type'    => 'complaint_answered',
                'title'   => 'تم الرد على شكواك',
                'body'    => $reply,
            ]);

            return [
                'status'    => 'answered',
                'complaint' => $complaint->fresh(['complainant.image', 'reportedUser.image', 'repliedByAdmin']),
            ];
        });
    }

    // ─── عدد الشكاوي الغير مردود عليها ──────────────────────────────────────────

    public function countUnanswered(): int
    {
        return Complaint::query()->where('status', 'pending')->count();
    }
}
