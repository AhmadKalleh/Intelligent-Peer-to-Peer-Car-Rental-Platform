<?php
// app/Models/BookingLocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingLocation extends Model
{
    protected $fillable = [
        'booking_id',
        'host_lat',
        'host_lng',
        'host_updated_at',
        'guest_lat',
        'guest_lng',
        'guest_updated_at',
    ];

    protected $casts = [
        'host_lat'         => 'decimal:7',
        'host_lng'         => 'decimal:7',
        'host_updated_at'  => 'datetime',
        'guest_lat'        => 'decimal:7',
        'guest_lng'        => 'decimal:7',
        'guest_updated_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
