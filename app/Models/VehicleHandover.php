<?php
// app/Models/VehicleHandover.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleHandover extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'code_hash',
        'status',
        'host_generated_at',
        'guest_scanned_at',
        'expires_at',
    ];

    protected $casts = [
        'host_generated_at' => 'datetime',
        'guest_scanned_at'  => 'datetime',
        'expires_at'        => 'datetime',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Relations
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
