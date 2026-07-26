<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponUseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coupon_uses')->insert([
            [
                'coupon_id' => 1,
                'user_id' => 2,
                'booking_id' => 1,
                'discount_applied' => 10.00,
                'used_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'coupon_id' => 2,
                'user_id' => 2,
                'booking_id' => 2,
                'discount_applied' => 50.00,
                'used_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'coupon_id' => 3,
                'user_id' => 2,
                'booking_id' => 3,
                'discount_applied' => 25.00,
                'used_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'coupon_id' => 4,
                'user_id' => 2,
                'booking_id' => 4,
                'discount_applied' => 100.00,
                'used_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
