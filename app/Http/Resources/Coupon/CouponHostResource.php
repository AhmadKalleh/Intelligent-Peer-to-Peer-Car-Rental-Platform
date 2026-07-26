<?php
// app/Http/Resources/Coupon/CouponHostResource.php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponHostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'discount_type'     => $this->discount_type,
            'discount_value'    => (float) $this->discount_value,
            'min_booking_days'  => (int) $this->min_booking_days,
            'max_uses'          => $this->max_uses,
            'used_count'        => (int) $this->used_count,
            'remaining_uses'    => $this->max_uses
                                    ? $this->max_uses - $this->used_count
                                    : null,
            'is_active'         => (bool) $this->is_active,
            'validity' => $this->formatValidity(),
            // ─── حالة الكوبون ─────────────────────────────
            'status'            => $this->resolveStatus(),

            'created_at'        => $this->created_at?->format('M Y'),
        ];
    }

    private function resolveStatus(): string
    {
        if (!$this->is_active) return 'inactive';

        if (now()->lt($this->valid_from)) return 'scheduled';

        if ($this->valid_until && now()->gt($this->valid_until)) return 'expired';

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return 'exhausted';

        return 'active';
    }

    private function formatValidity(): ?string
    {
        if (!$this->valid_from && !$this->valid_until) {
            return null;
        }

        if ($this->valid_from && $this->valid_until) {
        return $this->valid_from->format('M d, Y') . ' - ' . $this->valid_until->format('M d, Y');        }

        if ($this->valid_from) {
            return 'From ' . $this->valid_from->format('M d, Y');
        }

        return 'Until ' . $this->valid_until->format('M d, Y');
    }
}
