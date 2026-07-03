<?php

namespace App\Repositories\Complaint\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ComplaintAdminRepositoryInterface
{
    public function getComplaintsOverview(int $perPage, int $flagThreshold): array;

    public function getUnanswered(int $perPage): LengthAwarePaginator;

    public function getAnswered(int $perPage): LengthAwarePaginator;

    public function getFlaggedUsers(int $threshold): Collection;

    public function replyToComplaint(int $complaintId, string $reply, int $adminId): array;

    public function countUnanswered(): int;
}
