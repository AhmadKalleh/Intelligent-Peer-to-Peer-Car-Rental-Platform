<?php

namespace App\Services\User\Guest;

use App\Repositories\User\Interfaces\UserGuestRepositoryInterface;

class UserGuestService
{
    public function __construct(
        protected UserGuestRepositoryInterface $_userGuestRepository
    ) {}

    // ─── Show Guest Details ───────────────────────────────────────────────────

    public function showGuestDetails(int $userId): array
    {
        $guest = $this->_userGuestRepository->showGuestDetails($userId);

        return [
            'data'    => $guest,
            'message' => 'Guest details retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ─── Change Guest Password ────────────────────────────────────────────────

    public function changeGuestPassword(int $userId, array $data): array
    {
        $result = $this->_userGuestRepository->changeGuestPassword($userId, $data);

        if ($result['status'] === 'not_guest') {
            return ['data' => [], 'message' => 'User is not a guest.', 'code' => 422];
        }

        return [
            'data'    => [],
            'message' => 'Guest password changed successfully.',
            'code'    => 200,
        ];
    }
}
