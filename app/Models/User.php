<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'google_id',
        'status',
        'auth_provider',
        'email_verified_at',
        'verification_code',
        'verification_code_expires_at',
        'verification_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'            => 'datetime',
        'password'                     => 'hashed',
        'verification_code_expires_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function host(): HasOne
    {
        return $this->hasOne(Host::class);
    }

    public function couponUses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    public function recentSearches(): HasMany
    {
        return $this->hasMany(RecentSearch::class)->latest('searched_at');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    // ← جديد: ليستات المفضلة
    public function favoriteLists(): HasMany
    {
        return $this->hasMany(FavoriteList::class);
    }

    // ← جديد: كل عناصر المفضلة
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
