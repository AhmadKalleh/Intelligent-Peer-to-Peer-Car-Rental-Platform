<?php
// app/Models/Coupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'host_id',
        'code',
        'discount_type',
        'discount_value',
        'min_booking_days',
        'max_uses',
        'used_count',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'min_booking_days' => 'integer',
        'max_uses'         => 'integer',
        'used_count'       => 'integer',
        'is_active'        => 'boolean',
        'valid_from'       => 'datetime',
        'valid_until'      => 'datetime',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Relations
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Helpers
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // هل الكوبون نشط وصالح الآن؟
    public function isValid(): bool
    {
        if (!$this->is_active) return false;

        if (now()->lt($this->valid_from)) return false;

        if ($this->valid_until && now()->gt($this->valid_until)) return false;

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;

        return true;
    }

    // حساب قيمة الخصم
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($subtotal * ($this->discount_value / 100), 2);
        }

        // fixed: لا يتجاوز الـ subtotal
        return min((float) $this->discount_value, $subtotal);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Scopes
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                        ->where('valid_from', '<=', now())
                        ->where(fn($q) =>
                            $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', now())
                        );
    }

    public function scopeNotExhausted($query)
    {
        return $query->where(fn($q) =>
            $q->whereNull('max_uses')
                ->orWhereColumn('used_count', '<', 'max_uses')
        );
    }
}
