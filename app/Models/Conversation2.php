<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation2 extends Model
{
    use HasFactory;
    protected $table = 'conversations2';

    protected $fillable = [
        'user_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message2::class)->orderBy('created_at');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function unreadCount(): int
    {
        return $this->messages()
            ->where('sender', 'ai')
            ->where('is_read', false)
            ->count();
    }
}
