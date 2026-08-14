<?php

// database/seeders/BookingAndReviewSeeder.php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Host;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingAndReviewSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================
        // 1. جلب الـ Guests الموجودين
        // =========================================================

        $guests = User::role('guest')
            ->take(10)
            ->get();

        if ($guests->isEmpty()) {
            $this->command->warn(
                'No guests found! Creating a test guest...'
            );

            $testGuest = User::firstOrCreate(
                [
                    'email' => 'guest@test.com',
                ],
                [
                    'full_name'         => 'Test Guest',
                    'password'          => Hash::make('password'),
                    'auth_provider'     => 'local',
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if (!$testGuest->hasRole('guest')) {
                $testGuest->assignRole('guest');
            }

            $guests = User::role('guest')
                ->take(10)
                ->get();
        }

        // =========================================================
        // 2. جلب السيارات الموجودة
        // =========================================================

        $vehicles = Vehicle::where('listing_status', 'listed')
            ->where('admin_review_status', 'approved')
            ->take(10)
            ->get();

        if ($vehicles->isEmpty()) {
            $this->command->warn(
                'No vehicles found! Please seed vehicles first.'
            );

            return;
        }

        // =========================================================
        // 3. إنشاء الحجوزات العادية
        //
        // لكل سيارة:
        // COMPLETED
        // CANCELLED
        // CONFIRMED
        // ACTIVE
        // =========================================================

        foreach ($vehicles as $vehicle) {

            $vehicleGuests = $guests
                ->shuffle()
                ->take(min(4, $guests->count()))
                ->values();

            if ($vehicleGuests->count() < 4) {
                $this->command->warn(
                    "Vehicle #{$vehicle->id} needs at least 4 guests."
                );

                continue;
            }

            // =====================================================
            // 1. COMPLETED
            // =====================================================

            $this->createCompletedBooking(
                vehicle: $vehicle,
                guest: $vehicleGuests[0],
            );

            // =====================================================
            // 2. CANCELLED
            // =====================================================

            $this->createCancelledBooking(
                vehicle: $vehicle,
                guest: $vehicleGuests[1],
            );

            // =====================================================
            // 3. CONFIRMED
            // =====================================================

            $this->createConfirmedBooking(
                vehicle: $vehicle,
                guest: $vehicleGuests[2],
            );

            // =====================================================
            // 4. ACTIVE
            // =====================================================

            $this->createActiveBooking(
                vehicle: $vehicle,
                guest: $vehicleGuests[3],
            );
        }

        // =========================================================
        // 4. إنشاء حجز Active مخصص لأحمد
        // =========================================================

        $this->createAhmedActiveBooking();

        // =========================================================
        // SUCCESS
        // =========================================================

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            '✅ Bookings seeded successfully!'
        );

        $this->command->info(
            '✅ Every vehicle has:'
        );

        $this->command->info(
            '   ✓ completed booking'
        );

        $this->command->info(
            '   ✓ cancelled booking'
        );

        $this->command->info(
            '   ✓ confirmed booking'
        );

        $this->command->info(
            '   ✓ active booking'
        );

        $this->command->info(
            '=============================================='
        );
    }

    // =============================================================
    // ACTIVE BOOKING FOR AHMED
    // =============================================================

    private function createAhmedActiveBooking(): void
    {
        // =========================================================
        // 1. جلب أحمد
        // =========================================================

        $ahmad = User::where(
            'email',
            'ahmad@host.com'
        )->first();

        if (!$ahmad) {
            $this->command->error(
                'Ahmed user not found: ahmad@host.com'
            );

            return;
        }

        // =========================================================
        // 2. جلب Host الخاص بأحمد
        // =========================================================

        $host = Host::where(
            'user_id',
            $ahmad->id
        )->first();

        if (!$host) {
            $this->command->error(
                "Host record for Ahmed was not found."
            );

            return;
        }

        // =========================================================
        // 3. إنشاء / جلب Guest مخصص للاختبار
        // =========================================================

        $guest = User::firstOrCreate(
            [
                'email' => 'guest@test.com',
            ],
            [
                'full_name'         => 'Test Guest',
                'password'          => Hash::make('password'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        if (!$guest->hasRole('guest')) {
            $guest->assignRole('guest');
        }

        // =========================================================
        // 4. جلب سيارة تابعة لأحمد
        // =========================================================

        $vehicle = Vehicle::where(
            'host_id',
            $host->id
        )
            ->where(
                'listing_status',
                'listed'
            )
            ->where(
                'admin_review_status',
                'approved'
            )
            ->first();

        if (!$vehicle) {
            $this->command->error(
                'No approved listed vehicle found for Ahmed.'
            );

            return;
        }

        // =========================================================
        // 5. التأكد أن السيارة لا تحتوي Active Booking حالياً
        // =========================================================

        $today = now()->toDateString();

        $existingActiveBooking = Booking::where(
            'vehicle_id',
            $vehicle->id
        )
            ->where(
                'status',
                'active'
            )
            ->whereDate(
                'start_date',
                '<=',
                $today
            )
            ->whereDate(
                'end_date',
                '>=',
                $today
            )
            ->first();

        if ($existingActiveBooking) {

            $this->command->warn(
                "Vehicle #{$vehicle->id} already has an active booking."
            );

            $this->command->info(
                "Existing Booking ID: {$existingActiveBooking->id}"
            );

            return;
        }

        // =========================================================
        // 6. تاريخ الحجز
        //
        // بدأ قبل يومين
        // ينتهي بعد 5 أيام
        //
        // لذلك هو Active فعلياً الآن.
        // =========================================================

        $startDate = now()->subDays(2);

        $endDate = now()->addDays(5);

        // =========================================================
        // 7. إنشاء الحجز
        // =========================================================

        $booking = $this->createBookingWithRelations(
            vehicle: $vehicle,
            guest: $guest,
            status: 'active',
            startDate: $startDate,
            endDate: $endDate,
            createPayment: true,
            createReview: false,
        );

        // =========================================================
        // 8. عرض المعلومات في Terminal
        // =========================================================

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            '✅ ACTIVE BOOKING CREATED FOR AHMED'
        );

        $this->command->info(
            "Ahmed User ID: {$ahmad->id}"
        );

        $this->command->info(
            "Host ID: {$host->id}"
        );

        $this->command->info(
            "Vehicle ID: {$vehicle->id}"
        );

        $this->command->info(
            "Vehicle: {$vehicle->make} {$vehicle->model}"
        );

        $this->command->info(
            "Guest ID: {$guest->id}"
        );

        $this->command->info(
            "Guest Email: {$guest->email}"
        );

        $this->command->info(
            "Booking ID: {$booking->id}"
        );

        $this->command->info(
            "Start Date: {$booking->start_date}"
        );

        $this->command->info(
            "End Date: {$booking->end_date}"
        );

        $this->command->info(
            "Status: {$booking->status}"
        );

        $this->command->info(
            "Total Days: {$booking->total_days}"
        );

        $this->command->info(
            "Total Amount: {$booking->total_amount}"
        );

        $this->command->info(
            '=============================================='
        );
    }

    // =============================================================
    // COMPLETED BOOKING
    // =============================================================

    private function createCompletedBooking(
        Vehicle $vehicle,
        User $guest,
    ): void {

        $endDate = now()->subDays(
            rand(2, 10)
        );

        $startDate = $endDate->copy()->subDays(
            rand(2, 7)
        );

        $this->createBookingWithRelations(
            vehicle: $vehicle,
            guest: $guest,
            status: 'completed',
            startDate: $startDate,
            endDate: $endDate,
            createPayment: true,
            createReview: true,
        );
    }

    // =============================================================
    // CANCELLED BOOKING
    // =============================================================

    private function createCancelledBooking(
        Vehicle $vehicle,
        User $guest,
    ): void {

        $startDate = now()->subDays(
            rand(10, 30)
        );

        $endDate = $startDate->copy()->addDays(
            rand(2, 7)
        );

        $this->createBookingWithRelations(
            vehicle: $vehicle,
            guest: $guest,
            status: 'cancelled',
            startDate: $startDate,
            endDate: $endDate,
            createPayment: false,
            createReview: false,
        );
    }

    // =============================================================
    // CONFIRMED BOOKING
    // =============================================================

    private function createConfirmedBooking(
        Vehicle $vehicle,
        User $guest,
    ): void {

        $startDate = now()->addDays(
            rand(2, 7)
        );

        $endDate = $startDate->copy()->addDays(
            rand(2, 7)
        );

        $this->createBookingWithRelations(
            vehicle: $vehicle,
            guest: $guest,
            status: 'confirmed',
            startDate: $startDate,
            endDate: $endDate,
            createPayment: true,
            createReview: false,
        );
    }

    // =============================================================
    // ACTIVE BOOKING
    // =============================================================

    private function createActiveBooking(
        Vehicle $vehicle,
        User $guest,
    ): void {

        $startDate = now()->subDays(
            rand(1, 3)
        );

        $endDate = now()->addDays(
            rand(2, 5)
        );

        $this->createBookingWithRelations(
            vehicle: $vehicle,
            guest: $guest,
            status: 'active',
            startDate: $startDate,
            endDate: $endDate,
            createPayment: true,
            createReview: false,
        );
    }

    // =============================================================
    // CREATE BOOKING WITH RELATIONS
    // =============================================================

    private function createBookingWithRelations(
        Vehicle $vehicle,
        User $guest,
        string $status,
        $startDate,
        $endDate,
        bool $createPayment = false,
        bool $createReview = false,
    ): Booking {

        // =========================================================
        // TOTAL DAYS
        // =========================================================

        $totalDays = max(
            1,
            $startDate->diffInDays($endDate)
        );

        // =========================================================
        // DATES
        // =========================================================

        $startDateString =
            $startDate->toDateString();

        $endDateString =
            $endDate->toDateString();

        // =========================================================
        // PRICE
        // =========================================================

        $basePricePerDay =
            (float) $vehicle->base_price_per_day;

        $subtotal =
            round(
                $basePricePerDay * $totalDays,
                2
            );

        // =========================================================
        // DELIVERY
        // =========================================================

        $deliveryType =
            rand(0, 1) === 1
                ? 'delivery'
                : 'pickup';

        /*
        |--------------------------------------------------------------------------
        | إذا السيارة لا تدعم التوصيل
        |--------------------------------------------------------------------------
        */

        if (!$vehicle->delivery_available) {
            $deliveryType = 'pickup';
        }

        $deliveryFee =
            $deliveryType === 'delivery'
                ? rand(5, 20)
                : 0;

        // =========================================================
        // PLATFORM FEE
        // =========================================================

        $platformFee =
            round(
                $subtotal * 0.10,
                2
            );

        // =========================================================
        // DISCOUNT
        // =========================================================

        $discountAmount = 0;

        if (
            in_array(
                $status,
                [
                    'confirmed',
                    'active',
                ],
                true
            )
        ) {

            $discountAmount =
                rand(0, 1) === 1
                    ? round(
                        $subtotal * 0.05,
                        2
                    )
                    : 0;
        }

        // =========================================================
        // TOTAL
        // =========================================================

        $totalAmount =
            round(
                $subtotal
                + $deliveryFee
                + $platformFee
                - $discountAmount,
                2
            );

        // =========================================================
        // CREATE BOOKING
        // =========================================================

        $booking = Booking::create([
            'vehicle_id' =>
                $vehicle->id,

            'host_id' =>
                $vehicle->host_id,

            'user_id' =>
                $guest->id,

            'start_date' =>
                $startDateString,

            'end_date' =>
                $endDateString,

            'total_days' =>
                $totalDays,

            'base_price_per_day' =>
                $basePricePerDay,

            'subtotal' =>
                $subtotal,

            'discount_amount' =>
                $discountAmount,

            'delivery_fee' =>
                $deliveryFee,

            'platform_fee' =>
                $platformFee,

            'total_amount' =>
                $totalAmount,

            'delivery_type' =>
                $deliveryType,

            'delivery_address' =>
                $deliveryType === 'delivery'
                    ? 'Street 5, Damascus'
                    : null,

            'delivery_lat' =>
                $deliveryType === 'delivery'
                    ? 33.5138
                    : null,

            'delivery_lng' =>
                $deliveryType === 'delivery'
                    ? 36.2765
                    : null,

            'status' =>
                $status,

            'cancellation_reason' =>
                $status === 'cancelled'
                    ? 'Changed my plans.'
                    : null,

            'cancelled_by' =>
                $status === 'cancelled'
                    ? 'guest'
                    : null,
        ]);

        // =========================================================
        // PAYMENT
        // =========================================================

        if ($createPayment) {

            Payment::create([
                'booking_id' =>
                    $booking->id,

                'payment_id' =>
                    'seed_' . Str::random(12),

                'amount' =>
                    $totalAmount,

                'status' =>
                    'paid',

                'payment_url' =>
                    null,

                'paid_at' =>
                    $booking->created_at,
            ]);
        }

        // =========================================================
        // REVIEW
        // =========================================================

        if ($createReview) {

            $this->createReview(
                booking: $booking,
                vehicle: $vehicle,
                guest: $guest,
            );
        }

        return $booking;
    }

    // =============================================================
    // CREATE REVIEW
    // =============================================================

    private function createReview(
        Booking $booking,
        Vehicle $vehicle,
        User $guest,
    ): void {

        // =========================================================
        // RATINGS
        // =========================================================

        $subRatings = [

            'cleanliness_rating' =>
                round(
                    rand(35, 50) / 10,
                    1
                ),

            'maintenance_rating' =>
                round(
                    rand(35, 50) / 10,
                    1
                ),

            'comfort_rating' =>
                round(
                    rand(35, 50) / 10,
                    1
                ),

            'communication_rating' =>
                round(
                    rand(35, 50) / 10,
                    1
                ),

            'punctuality_rating' =>
                round(
                    rand(35, 50) / 10,
                    1
                ),
        ];

        // =========================================================
        // OVERALL
        // =========================================================

        $overallRating =
            round(
                array_sum($subRatings)
                / count($subRatings),
                1
            );

        // =========================================================
        // CREATE REVIEW
        // =========================================================

        Review::create([

            'booking_id' =>
                $booking->id,

            'vehicle_id' =>
                $vehicle->id,

            'host_id' =>
                $vehicle->host_id,

            'user_id' =>
                $guest->id,

            'overall_rating' =>
                $overallRating,

            'comment' =>
                $this->randomComment(),

            'is_visible' =>
                true,

            // Vehicle
            'cleanliness_rating' =>
                $subRatings[
                    'cleanliness_rating'
                ],

            'maintenance_rating' =>
                $subRatings[
                    'maintenance_rating'
                ],

            'comfort_rating' =>
                $subRatings[
                    'comfort_rating'
                ],

            // Host
            'communication_rating' =>
                $subRatings[
                    'communication_rating'
                ],

            'punctuality_rating' =>
                $subRatings[
                    'punctuality_rating'
                ],
        ]);
    }

    // =============================================================
    // RANDOM REVIEW COMMENT
    // =============================================================

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

        return $comments[
            array_rand($comments)
        ];
    }
}