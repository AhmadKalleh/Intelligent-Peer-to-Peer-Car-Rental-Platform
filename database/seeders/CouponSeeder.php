<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coupons')->insert([
            [
                'code' => 'WELCOME10',
                'host_id' => 1, // Assuming host_id 1 exists
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_booking_days' => 2,
                'max_uses' => 100,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SUMMER50',
                'host_id' => 1, // Assuming host_id 1 exists
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'min_booking_days' => 3,
                'max_uses' => 50,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonth(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'VIP25',
                'host_id' => 1, // Assuming host_id 1 exists
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'min_booking_days' => 1,
                'max_uses' => null, // غير محدود
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(6),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FLASH100',
                'host_id' => 1, // Assuming host_id 1 exists
                'discount_type' => 'fixed',
                'discount_value' => 100,
                'min_booking_days' => null,
                'max_uses' => 10,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'NEWUSER15',
                'host_id' => 1, // Assuming host_id 1 exists
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'min_booking_days' => 2,
                'max_uses' => 200,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        
    }
}
