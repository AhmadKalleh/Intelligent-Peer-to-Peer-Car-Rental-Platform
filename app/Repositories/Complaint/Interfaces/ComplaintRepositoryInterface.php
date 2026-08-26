<?php

namespace App\Repositories\Complaint\Interfaces;

interface ComplaintRepositoryInterface
{
    public function submitComplaint(int $userId, array $data): array;
}
