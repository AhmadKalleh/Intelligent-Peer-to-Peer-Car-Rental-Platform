<?php
// database/seeders/BookingAndReviewSeeder.php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Vehicle;
use App\Models\Host;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingAndReviewSeeder extends Seeder
{
    public function run(): void
    {
        // ─── جلب البيانات الموجودة ────────────────────────────
        $guests   = User::role('guest')->take(5)->get();
        $vehicles = Vehicle::where('listing_status', 'listed')
                            ->where('admin_review_status', 'approved')
                            ->take(10)
                            ->get();

        if ($guests->isEmpty() || $vehicles->isEmpty()) {
            $this->command->warn('No guests or vehicles found! Please seed them first.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];

        foreach ($vehicles as $vehicle) {

            $vehicleGuests = $guests->random(min(3, $guests->count()));

            // ─── 3 حجوزات لكل سيارة ──────────────────────────
            foreach ($vehicleGuests as $index => $guest) {

                // ← جديد: أول حجز لكل سيارة مضمون إنو confirmed
                // وبتواريخ حالية (مش بالماضي) حتى يصلح لتجربة
                // ميزة الاستلام (handover) مباشرة. الباقي عشوائي
                // متل ما كان.
                $status = $index === 0 ? 'confirmed' : $statuses[array_rand($statuses)];

                if ($status === 'confirmed') {
                    $startDate = now()->toDateString();
                    $totalDays = rand(2, 7);
                    $endDate   = now()->addDays($totalDays)->toDateString();
                } else {
                    $startDate = now()->subDays(rand(10, 60))->toDateString();
                    $totalDays = rand(1, 7);
                    $endDate   = now()->subDays(rand(1, 9))->toDateString();
                }

                $basePricePerDay = (float) $vehicle->base_price_per_day;
                $subtotal       = $basePricePerDay * $totalDays;
                $deliveryFee    = rand(0, 1) ? rand(5, 20) : 0;
                $platformFee    = round($subtotal * 0.10, 2);
                $discountAmount = $status !== 'confirmed' && rand(0, 1) ? round($subtotal * 0.05, 2) : 0;
                $totalAmount    = $subtotal + $deliveryFee + $platformFee - $discountAmount;
                $deliveryType   = rand(0, 1) ? 'pickup' : 'delivery';

                $booking = Booking::create([
                    'vehicle_id'       => $vehicle->id,
                    'host_id'          => $vehicle->host_id,
                    'user_id'          => $guest->id,
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                    'total_days'       => $totalDays,
                    'base_price_per_day' => $basePricePerDay,
                    'subtotal'         => $subtotal,
                    'discount_amount'  => $discountAmount,
                    'delivery_fee'     => $deliveryFee,
                    'platform_fee'     => $platformFee,
                    'total_amount'     => $totalAmount,
                    'delivery_type'    => $deliveryType,
                    'delivery_address' => $deliveryType === 'delivery' ? 'Street 5, Damascus' : null,
                    'delivery_lat'     => $deliveryType === 'delivery' ? 33.5138 : null,
                    'delivery_lng'     => $deliveryType === 'delivery' ? 36.2765 : null,
                    'status'           => $status,
                    'cancellation_reason' => $status === 'cancelled' ? 'Changed my plans.' : null,
                    'cancelled_by'     => $status === 'cancelled' ? 'guest' : null,
                ]);

                // ─── Payment لكل حجز غير pending/cancelled ────
                // (← جديد: بدون هاد، أي endpoint بيعمل eager load
                // لعلاقة payment رح يرجع null لهاد الحجوزات وقت
                // ما لازم يكون فيها دفعة فعلية، متل confirmed/active/completed)
                if (in_array($status, ['confirmed', 'active', 'completed'])) {
                    Payment::create([
                        'booking_id'  => $booking->id,
                        'payment_id'  => 'seed_' . Str::random(12),
                        'amount'      => $totalAmount,
                        'status'      => 'paid',
                        'payment_url' => null,
                        'paid_at'     => $booking->created_at,
                    ]);
                }

                // ─── Review فقط للحجوزات المكتملة ────────────
                if ($status === 'completed') {
                    $subRatings = [
                        'cleanliness_rating'=>round(rand(35, 50) / 10, 1),
                        'maintenance_rating'=>round(rand(35, 50) / 10, 1),
                        'comfort_rating'=>round(rand(35, 50) / 10, 1),
                        'communication_rating' => round(rand(35, 50) / 10, 1),
                        'punctuality_rating' =>round(rand(35, 50) / 10, 1),
                    ];
                    Review::create([
                        'booking_id'           => $booking->id,
                        'vehicle_id'           => $vehicle->id,
                        'host_id'              => $vehicle->host_id,
                        'user_id'              => $guest->id,
                        'overall_rating'       => round(array_sum($subRatings) / count($subRatings), 1), // 3.5 - 5.0
                        'comment'              => $this->randomComment(),
                        'is_visible'           => true,
                        // تقييم السيارة
                        'cleanliness_rating'   => $subRatings['cleanliness_rating'],
                        'maintenance_rating'   => $subRatings['maintenance_rating'],
                        'comfort_rating'       => $subRatings['comfort_rating'],
                        // تقييم الهوست
                        'communication_rating' => $subRatings['communication_rating'],
                        'punctuality_rating'   => $subRatings['punctuality_rating'],
                    ]);
                }
            }
        }

        $this->command->info('✅ Bookings and Reviews seeded successfully!');
        $this->command->info('   كل سيارة صار إلها حجز confirmed واحد على الأقل، جاهز لتجربة الاستلام.');
    }

    // ─── تعليقات عشوائية ──────────────────────────────────────
    private function randomComment(): string
    {
        $comments = [
            'Great car, very clean and well maintained!',
            'The host was very responsive and helpful.',
            'Amazing experience, will book again!',
            'Car was exactly as described, no issues at all.',
            'Very comfortable ride, highly recommend.',
            'Good value for money, smooth booking process.',
            'The car was in perfect condition.',
            'Host was punctual and very professional.',
            'Wonderful experience from start to finish.',
            'Clean car, great host, will definitely return!',
        ];

        return $comments[array_rand($comments)];
    }
}