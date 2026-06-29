<?php

namespace App\Http\Resources\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * يُستخدم عند إرجاع قائمة الليستات
 */
class FavoriteListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this['id'],
            'name'            => $this['name'],
            'favorites_count' => $this['favorites_count'],
            'thumbnail'       => $this['thumbnail'],
            'created_at'      => $this['created_at'],
        ];
    }
}
