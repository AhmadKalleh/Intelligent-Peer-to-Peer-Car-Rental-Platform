<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_user_id',
        'host_user_id',
        'last_message',
        'last_message_at',
        'guest_unread_count',
        'host_unread_count',
    ];

    protected $casts = [
        'last_message_at'    => 'datetime',
        'guest_unread_count' => 'integer',
        'host_unread_count'  => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────

    public function guestUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function hostUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * يُحدِّث آخر رسالة وعداد الغير مقروءة للطرف المستقبِل
     */
    public function touchLastMessage(string $body, int $senderUserId): void
    {
        $isGuestSending = (int) $this->guest_user_id === $senderUserId;

        $this->update([
            'last_message'      => $body,
            'last_message_at'   => now(),
            // الطرف المستقبِل هو الذي يزداد عداده
            'guest_unread_count' => $isGuestSending
                ? $this->guest_unread_count                // لم يتغيّر
                : $this->guest_unread_count + 1,
            'host_unread_count'  => $isGuestSending
                ? $this->host_unread_count + 1
                : $this->host_unread_count,                // لم يتغيّر
        ]);
    }

    /**
     * عند قراءة الرسائل: نصفّر عداد الطرف الذي يقرأ
     */
    public function resetUnreadCount(int $readerUserId): void
    {
        if ((int) $this->guest_user_id === $readerUserId) {
            $this->update(['guest_unread_count' => 0]);
        } else {
            $this->update(['host_unread_count' => 0]);
        }
    }
}
