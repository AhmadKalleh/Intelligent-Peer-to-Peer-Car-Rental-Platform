<?php

namespace App\Repositories\User\Interfaces;

use App\Models\Host;

interface UserHostRepositoryInterface
{
    public function showHostDetails(int $hostId): Host;

    public function changeHostPassword(int $userId, array $data): array;

    // ← جديد: يرجع id تبع صف الهوست (hosts.id) اعتمادًا على user_id المسجّل دخوله
    public function getHostId(int $userId): ?int;
}
