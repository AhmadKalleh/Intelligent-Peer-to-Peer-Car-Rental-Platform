<?php
// app/Repositories/Notification/Interfaces/NotificationRepositoryInterface.php

namespace App\Repositories\Notification\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function index(int $userId, int $perPage): array;
    public function markAllAsRead(int $userId, array $ids): int;
    public function unreadCount(int $userId): int;
    public function send(
        int     $userId,
        string  $type,
        string  $title,
        ?string $body,
    ): void;
}
