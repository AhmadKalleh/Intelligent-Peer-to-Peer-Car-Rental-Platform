<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'brand',
        'model',
        'year',
        'color',
        'license_plate',
        'transmission',
        'fuel_type',
        'seats',
        'daily_price',
        'description',
        'location',
        'latitude',
        'longitude',
        'status',
        'is_active',
    ];

    protected $casts = [
        'daily_price' => 'decimal:2',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
        'is_active'   => 'boolean',
        'year'        => 'integer',
        'seats'       => 'integer',
    ];

    // =====================
    //      Relationships
    // =====================

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(Image::class)->where('is_primary', true);
    }

    public function features(): HasMany
    {
        return $this->hasMany(VehicleFeature::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(VehicleAvailability::class);
    }

    public function customPricings(): HasMany
    {
        return $this->hasMany(VehicleCustomPricing::class);
    }
}
