<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'host_id',
        'user_id',
        'start_date',
        'end_date',
        'total_days',
        'base_price_per_day',
        'subtotal',
        'discount_amount',
        'delivery_fee',
        'platform_fee',
        'total_amount',
        'delivery_type',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'status',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'total_days'         => 'integer',
        'base_price_per_day' => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'delivery_fee'       => 'decimal:2',
        'platform_fee'       => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'delivery_lat'       => 'decimal:7',
        'delivery_lng'       => 'decimal:7',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Relations
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // الضيف الذي قام بالحجز
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // السيارة المحجوزة
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // المضيف
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // الكوبون إن وجد
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // التقييم الخاص بهذا الحجز
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function couponUse(): HasOne
    {
        return $this->hasOne(CouponUse::class);
    }
}
