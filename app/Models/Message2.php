<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message2 extends Model
{
    use HasFactory;
    protected $table = 'messages2';
    public const SENDER_GUEST = 'guest';
    public const SENDER_AI    = 'ai';

    protected $fillable = [
        'conversation_id',
        'sender',
        'content',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation2::class);
    }
}
