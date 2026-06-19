<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'path',
        'url',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'sort_order'  => 'integer',
    ];

    // =====================
    //      Relationships
    // =====================

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
