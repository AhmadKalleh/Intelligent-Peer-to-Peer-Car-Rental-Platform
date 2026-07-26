<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'available_from',
        'available_to',
        'is_blocked',
        'block_reason',
        'type',
        'blocked_by'
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_to' => 'date',
        'is_blocked' => 'boolean',
    ];

    // فترة التوفر تنتمي لمركبة
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
