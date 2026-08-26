<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Host;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingAndReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $hostId = 1;
            $userId = 2;

            // نستخدم اليوم كبداية، حتى يبقى الـ Seeder ديناميكياً
            $today = Carbon::today();

            /*
            |--------------------------------------------------------------------------
            | بيانات الحجوزات
            |--------------------------------------------------------------------------
            |
            | 1. confirmed + delivery + vehicle 1
            | 2. confirmed + pickup   + vehicle 3
            | 3. pending
            | 4. active
            | 5. completed
            |
            */

            $bookings = [
                [
                    'vehicle_id' => 1,
                    'start_date' => $today->copy()->addDays(1),
                    'end_date' => $today->copy()->addDays(4),
                    'total_days' => 4,

                    'base_price_per_day' => 100,
                    'subtotal' => 400,
                    'discount_amount' => 20,
                    'delivery_fee' => 30,
                    'platform_fee' => 20,
                    'total_amount' => 430,

                    'delivery_type' => 'delivery',
                    'delivery_address' => 'Rotterdam, Netherlands',
                    'delivery_lat' => 51.9244,
                    'delivery_lng' => 4.4777,

                    'status' => 'confirmed',
                    'cancellation_reason' => null,
                    'cancelled_by' => null,
                ],

                [
                    'vehicle_id' => 3,
                    'start_date' => $today->copy()->addDays(6),
                    'end_date' => $today->copy()->addDays(8),
                    'total_days' => 3,

                    'base_price_per_day' => 120,
                    'subtotal' => 360,
                    'discount_amount' => 30,
                    'delivery_fee' => 0,
                    'platform_fee' => 20,
                    'total_amount' => 350,

                    'delivery_type' => 'pickup',
                    'delivery_address' => null,
                    'delivery_lat' => null,
                    'delivery_lng' => null,

                    'status' => 'confirmed',
                    'cancellation_reason' => null,
                    'cancelled_by' => null,
                ],

                [
                    'vehicle_id' => 1,
                    'start_date' => $today->copy()->addDays(10),
                    'end_date' => $today->copy()->addDays(12),
                    'total_days' => 3,

                    'base_price_per_day' => 100,
                    'subtotal' => 300,
                    'discount_amount' => 0,
                    'delivery_fee' => 25,
                    'platform_fee' => 15,
                    'total_amount' => 340,

                    'delivery_type' => 'delivery',
                    'delivery_address' => 'The Hague, Netherlands',
                    'delivery_lat' => 52.0705,
                    'delivery_lng' => 4.3007,

                    'status' => 'pending',
                    'cancellation_reason' => null,
                    'cancelled_by' => null,
                ],

                [
                    'vehicle_id' => 3,
                    'start_date' => $today->copy()->subDays(1),
                    'end_date' => $today->copy()->addDays(2),
                    'total_days' => 4,

                    'base_price_per_day' => 120,
                    'subtotal' => 480,
                    'discount_amount' => 40,
                    'delivery_fee' => 0,
                    'platform_fee' => 25,
                    'total_amount' => 465,

                    'delivery_type' => 'pickup',
                    'delivery_address' => null,
                    'delivery_lat' => null,
                    'delivery_lng' => null,

                    'status' => 'active',
                    'cancellation_reason' => null,
                    'cancelled_by' => null,
                ],

                [
                    'vehicle_id' => 1,
                    'start_date' => $today->copy()->subDays(10),
                    'end_date' => $today->copy()->subDays(7),
                    'total_days' => 4,

                    'base_price_per_day' => 100,
                    'subtotal' => 400,
                    'discount_amount' => 50,
                    'delivery_fee' => 30,
                    'platform_fee' => 20,
                    'total_amount' => 400,

                    'delivery_type' => 'delivery',
                    'delivery_address' => 'Amsterdam, Netherlands',
                    'delivery_lat' => 52.3676,
                    'delivery_lng' => 4.9041,

                    'status' => 'completed',
                    'cancellation_reason' => null,
                    'cancelled_by' => null,
                ],
            ];

            foreach ($bookings as $index => $data) {

                /*
                |--------------------------------------------------------------------------
                | 1. إنشاء الحجز
                |--------------------------------------------------------------------------
                */

                $booking = Booking::create([
                    'vehicle_id' => $data['vehicle_id'],
                    'host_id' => $hostId,
                    'user_id' => $userId,

                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_days' => $data['total_days'],

                    'base_price_per_day' => $data['base_price_per_day'],
                    'subtotal' => $data['subtotal'],
                    'discount_amount' => $data['discount_amount'],
                    'delivery_fee' => $data['delivery_fee'],
                    'platform_fee' => $data['platform_fee'],
                    'total_amount' => $data['total_amount'],

                    'delivery_type' => $data['delivery_type'],
                    'delivery_address' => $data['delivery_address'],
                    'delivery_lat' => $data['delivery_lat'],
                    'delivery_lng' => $data['delivery_lng'],

                    'status' => $data['status'],

                    'cancellation_reason' => $data['cancellation_reason'],
                    'cancelled_by' => $data['cancelled_by'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | 2. Payment
                |--------------------------------------------------------------------------
                */

                DB::table('payments')->insert([
                    'booking_id' => $booking->id,

                    // 1, 2, 3, 4, 5
                    'payment_id' => (string) ($index + 1),

                    'amount' => $data['total_amount'],

                    'status' => match ($data['status']) {
                        'pending' => 'pending',
                        'confirmed', 'active', 'completed' => 'paid',
                        default => 'pending',
                    },

                    'payment_url' => null,

                    'gateway_response' => json_encode([
                        'seed' => true,
                        'booking_number' => $index + 1,
                    ]),

                    'paid_at' => $data['status'] === 'pending'
                        ? null
                        : now(),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | 3. Coupon Use
                |--------------------------------------------------------------------------
                |
                | نفترض أن coupon_id = 1 موجود مسبقاً.
                |
                | إذا كان جدول coupons يحتوي على كوبون ID = 1
                | سيتم ربطه بالحجوزات الخمسة.
                |
                */

                // DB::table('coupon_uses')->insert([
                //     'coupon_id' => 1,
                //     'user_id' => $userId,
                //     'booking_id' => $booking->id,
                //     'discount_applied' => $data['discount_amount'],
                //     'used_at' => now(),

                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);
            }
        });
    }
}
