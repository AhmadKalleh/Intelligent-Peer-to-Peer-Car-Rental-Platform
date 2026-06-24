<?php
// app/Models/Review.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'host_id',
        'user_id',
        'overall_rating',
        'comment',
        'is_visible',
        'cleanliness_rating',
        'maintenance_rating',
        'comfort_rating',
        'communication_rating',
        'punctuality_rating',
    ];

    protected $casts = [
        'overall_rating'       => 'decimal:1',
        'cleanliness_rating'   => 'decimal:1',
        'maintenance_rating'   => 'decimal:1',
        'comfort_rating'       => 'decimal:1',
        'communication_rating' => 'decimal:1',
        'punctuality_rating'   => 'decimal:1',
        'is_visible'           => 'boolean',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Relations
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // الحجز المرتبط بالتقييم
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // السيارة التي تم تقييمها
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // المضيف الذي تم تقييمه
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // الضيف الذي قام بالتقييم
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
