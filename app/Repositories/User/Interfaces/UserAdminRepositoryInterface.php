<?php

namespace App\Repositories\User\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserAdminRepositoryInterface
{
    public function addUser(array $data): array;

    public function promoteGuestToHost(int $userId): array;

    public function deleteGuest(int $userId): array;

    public function deleteHost(int $userId): array;

    public function toggleGuestStatus(int $userId): array;

    public function getUsers(int $perPage): LengthAwarePaginator;
}
