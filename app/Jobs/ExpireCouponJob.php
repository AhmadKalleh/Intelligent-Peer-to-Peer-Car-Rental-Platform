<?php
// app/Jobs/Coupon/ExpireCouponJob.php

namespace App\Jobs;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class ExpireCouponJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $couponId,
        public int $hostId,
    ) {}

    public function handle(): void
    {
        $coupon = Coupon::find($this->couponId);

        // ── تحقق أن الكوبون لم يُحذف أو يُعدّل ─────────
        if (!$coupon) return;

        // ── تحقق أن الصلاحية انتهت فعلاً ────────────────
        if (!$coupon->valid_until || now()->lt($coupon->valid_until)) return;

        // ── تعطيل الكوبون ─────────────────────────────────
        $coupon->update(['is_active' => false]);

        // // ── إشعار الهوست ──────────────────────────────────
        // Notification::create([
        //     'user_id' => $coupon->host->user_id,
        //     'type'    => 'coupon_expired',
        //     'title'   => 'Coupon Expired',
        //     'body'    => "Your coupon [{$coupon->code}] has expired and been deactivated.",
        // ]);

        // ── مسح الكاش ─────────────────────────────────────
        Redis::del("host:{$this->hostId}:coupons");
    }
}
