<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleCustomPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date_from',
        'date_to',
        'price_per_day',
        'reason',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    // السعر المخصص ينتمي لمركبة
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

}
