<?php

namespace App\Services\User\Host;

use App\Repositories\User\Interfaces\UserHostRepositoryInterface;

class UserHostService
{
    public function __construct(
        protected UserHostRepositoryInterface $_userHostRepository
    ) {}

    // ─── Show Host Details ────────────────────────────────────────────────────

    public function showHostDetails(int $hostId): array
    {
        $host = $this->_userHostRepository->showHostDetails($hostId);

        return [
            'data'    => $host,
            'message' => 'Host details retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ─── Change Host Password ─────────────────────────────────────────────────

    public function changeHostPassword(int $userId, array $data): array
    {
        $result = $this->_userHostRepository->changeHostPassword($userId, $data);

        if ($result['status'] === 'not_host') {
            return ['data' => [], 'message' => 'User is not a host.', 'code' => 422];
        }

        return [
            'data'    => [],
            'message' => 'Host password changed successfully.',
            'code'    => 200,
        ];
    }
}
