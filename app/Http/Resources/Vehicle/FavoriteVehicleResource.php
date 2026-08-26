<?php

namespace App\Http\Resources\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * يُستخدم لكل سيارة داخل ليستا المفضلة
 * يحتوي على vehicle_status المحدّث تلقائياً
 */
class FavoriteVehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this['id'],
            'make'               => $this['make'],
            'model'              => $this['model'],
            'year'               => $this['year'],
            'city'               => $this['city'],
            'base_price_per_day' => $this['base_price_per_day'],
            'rating_avg'         => $this['rating_avg'],
            'total_bookings'     => $this['total_bookings'],
            'primary_image'      => $this['primary_image'],

            /**
             * vehicle_status — حالة السيارة داخل المفضلة:
             *
             *  "available"   → متاحة للحجز  (قلب أحمر مضيء)
             *  "booked"      → محجوزة حالياً (قلب رمادي + badge "محجوزة")
             *  "unavailable" → غير متاحة / snoozed (قلب رمادي)
             *  "suspended"   → موقوفة إدارياً (قلب رمادي + badge "موقوفة")
             *  "deleted"     → محذوفة من التطبيق (يُخفى العنصر في الفرونت)
             */
            'vehicle_status'     => $this['vehicle_status'],
        ];
    }
}
