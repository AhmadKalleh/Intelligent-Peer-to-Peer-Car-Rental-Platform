<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RecentSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city',
        'airport_code',
        'lat',
        'lng',
        'search_type',
        'searched_at',
    ];

    protected $casts = [

        'searched_at' => 'datetime',
    ];

    // البحث ينتمي إلى مستخدم معين
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
