<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reported_user_id',
        'reason_key',
        'reason_subject',
        'reason_text',
        'details',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────

    // صاحب الشكوى
    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // المستخدم المشتكى عليه
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    // الأدمن الذي قام بالرد
    public function repliedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
