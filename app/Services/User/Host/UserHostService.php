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

    // ─── Get Host ID ← جديد ────────────────────────────────────────────────────
    public function getHostId(int $userId): array
    {
        $hostId = $this->_userHostRepository->getHostId($userId);

        if (!$hostId) {
            return [
                'data'    => [],
                'message' => 'You do not have a host account.',
                'code'    => 422,
            ];
        }

        return [
            'data'    => ['host_id' => $hostId],
            'message' => 'Host ID retrieved successfully.',
            'code'    => 200,
        ];
    }
}
