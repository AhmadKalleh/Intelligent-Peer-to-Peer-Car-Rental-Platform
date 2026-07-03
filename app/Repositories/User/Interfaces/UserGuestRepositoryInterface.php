<?php

namespace App\Repositories\User\Interfaces;

use App\Models\User;

interface UserGuestRepositoryInterface
{
    public function showGuestDetails(int $userId): User;

    public function changeGuestPassword(int $userId, array $data): array;
}
