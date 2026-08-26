<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FavoriteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    // الليستا تنتمي لمستخدم
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // العناصر داخل الليستا
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // السيارات داخل الليستا عبر pivot
    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'favorites')
                    ->withTimestamps();
    }
}
