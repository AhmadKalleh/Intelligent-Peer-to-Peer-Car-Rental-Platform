<?php
// app/Jobs/Coupon/NotifyGuestsAboutCouponJob.php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Host;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyGuestsAboutCouponJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int    $couponId,
        public int    $hostId,
        public string $couponCode,
        public string $discountType,
        public float  $discountValue,
        public ?string $validUntil,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $host = Host::with('user')->find($this->hostId);

        if (!$host) return;

        $hostName = $host->user->full_name;

        // ── جلب كل الغيست الذين حجزوا مع هذا الهوست ────
        $guestIds = Booking::where('host_id', $this->hostId)
            ->whereIn('status', ['completed', 'confirmed'])
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (empty($guestIds)) return;

        // ── قيمة الخصم بشكل مقروء ────────────────────────
        $discountText = $this->discountType === 'percentage'
            ? "{$this->discountValue}%"
            : "{$this->discountValue} SYP";

        // ── الصلاحية ──────────────────────────────────────
        $validUntilText = $this->validUntil
            ? "Valid until: " . \Carbon\Carbon::parse($this->validUntil)->format('Y-m-d')
            : 'No expiration date';

        // ── إشعار لكل غيست ───────────────────────────────
        foreach ($guestIds as $guestId) {
            $notificationService->send(
                userId         : $guestId,
                type           : 'coupon_available',
                title          : "🎉 Discount Coupon from {$hostName}",
                body           : "Use code {$this->couponCode} and get {$discountText} off. {$validUntilText}",
            );
        }
    }
}
