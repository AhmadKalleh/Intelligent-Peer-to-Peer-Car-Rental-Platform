<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_earnings',
        'available_balance',
        'rating_avg',
        'total_trips',
        'delivery_available',
        'delivery_fee_per_km',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'delivery_available' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // المضيف ينتمي إلى مستخدم
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // المضيف لديه العديد من المركبات
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // شهادة القيادة
    public function drivingLicense(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('type', 'driving_license');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
