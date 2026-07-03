<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'reviewed_by_user_id',
        'make',
        'model',
        'year',
        'color',
        'fuel_type',
        'transmission',
        'engine_capacity',
        'seats',
        'plate_number',
        'listing_status',
        'snoozed_until',
        'admin_review_status',
        'admin_rejection_reason',
        'reviewed_at',
        'base_price_per_day',
        'min_price',
        'max_price',
        'delivery_available',
        'delivery_fee',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'city',
        'guest_instructions',
        'total_reviews',
        'total_bookings',
        'rating_avg',
    ];

    protected $casts = [
        'snoozed_until'      => 'datetime',
        'reviewed_at'        => 'datetime',
        'delivery_available' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function primaryImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_primary', true);
    }

    public function adminReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'vehicle_features');
    }

    public function customPricings(): HasMany
    {
        return $this->hasMany(VehicleCustomPricing::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(VehicleAvailability::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function mechanicBooklet(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('type', 'mechanic_booklet');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ← جديد: الحجوزات (مطلوبة لحساب حالة السيارة في المفضلة)
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ← جديد: المفضلة
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeWithCurrentPrice($query)
    {
        return $query->selectRaw("
            COALESCE(
                (
                    SELECT cp.price_per_day
                    FROM vehicle_custom_pricings cp
                    WHERE cp.vehicle_id = vehicles.id
                    AND NOW() BETWEEN cp.date_from AND cp.date_to
                    ORDER BY cp.date_from DESC
                    LIMIT 1
                ),
                vehicles.base_price_per_day
            ) as current_price
        ");
    }

    public function scopeWithCustomPriceStatus($query): void
    {
        $query->selectRaw("
                EXISTS(
                    SELECT 1
                    FROM vehicle_custom_pricings cp
                    WHERE cp.vehicle_id = vehicles.id
                    AND NOW() BETWEEN cp.date_from AND cp.date_to
                ) as is_custom_price_active
            ");
    }

    public function scopeWithAllStarHost($query)
    {
        return $query->selectRaw("
            EXISTS (
                SELECT 1 FROM hosts
                WHERE hosts.id = vehicles.host_id
                AND hosts.rating_avg >= 4.8
                AND hosts.total_trips >= 20
            ) as is_all_star_host
        ");
    }
}
