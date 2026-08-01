<?php
// app/Repositories/Notification/NotificationRepository.php

namespace App\Repositories\Notification;

use App\Models\Notification;
use App\Repositories\Notification\Interfaces\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class NotificationRepository implements NotificationRepositoryInterface
{
    private function unreadCacheKey(int $userId): string
    {
        return "notifications:unread_count:{$userId}";
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $userId, int $perPage): array
    {
        // ── الغير مقروءة: كلها بدون pagination ───────────
        $unread = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->get();

        // ── المقروءة: مع pagination ───────────────────────
        $read = Notification::where('user_id', $userId)
            ->where('is_read', true)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return [
            'unread' => $unread,
            'read'   => $read,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MARK ALL AS READ
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function markAllAsRead(int $userId, array $ids): int
    {
        $updated = Notification::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // ── مسح كاش الـ unreadCount ───────────────────────
        Redis::del($this->unreadCacheKey($userId));

        return $updated;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UNREAD COUNT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function unreadCount(int $userId): int
    {
        $cacheKey = $this->unreadCacheKey($userId);
        $cached   = Redis::get($cacheKey);

        if ($cached !== null) {
            return (int) $cached;
        }

        $count = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        Redis::setex($cacheKey, 300, $count);

        return $count;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEND (خدمة داخلية)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function send(
        int     $userId,
        string  $type,
        string  $title,
        ?string $body          = null,
        ?string $notifiableType = null,
        ?int    $notifiableId  = null,
    ): void {
        Notification::create([
            'user_id'          => $userId,
            'type'             => $type,
            'title'            => $title,
            'body'             => $body,
            'is_read'          => false,
        ]);

        // ── مسح كاش unreadCount ───────────────────────────
        Redis::del($this->unreadCacheKey($userId));
    }
}
