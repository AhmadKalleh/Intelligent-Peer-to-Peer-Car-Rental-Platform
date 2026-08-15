<?php

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
        // 3. إنشاء الـ Guest المخصص للـ Confirmed Bookings
        // =========================================================

        $confirmedGuest = $this->getConfirmedGuest();

        $this->command->info(
            "Confirmed Guest ID: {$confirmedGuest->id}"
        );

        $this->command->info(
            "Confirmed Guest Email: {$confirmedGuest->email}"
        );

        // =========================================================
        // 4. إنشاء الحجوزات العادية
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
                guest: $confirmedGuest,
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
        // 5. إنشاء حجز Confirmed + Delivery مخصص لأحمد
        //
        // مهم:
        // هذا الحجز سيكون ملكاً للمستخدم ID = 3
        // وليس guest@test.com
        //
        // Status:
        // confirmed
        //
        // Delivery:
        // delivery
        //
        // Payment:
        // paid
        // =========================================================

        $this->createAhmedConfirmedDeliveryBooking();

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
            '=============================================='
        );
    }

    // =============================================================
    // CONFIRMED BOOKING GUEST
    // =============================================================

    private function getConfirmedGuest(): User
    {
        $guest = User::firstOrCreate(
            [
                'email' => 'guest@carrental.sy',
            ],
            [
                'full_name'         => 'Car Rental Guest',
                'password'          => Hash::make('password'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        if (!$guest->hasRole('guest')) {
            $guest->assignRole('guest');
        }

        return $guest;
    }

    // =============================================================
    // CONFIRMED + DELIVERY BOOKING FOR AHMED
    //
    // هذا هو الحجز المخصص للمستخدم:
    //
    // User:
    // ID = 3
    // Email = ahmad@host.com
    //
    // Status:
    // confirmed
    //
    // Delivery:
    // delivery
    //
    // Payment:
    // paid
    // =============================================================

    private function createAhmedConfirmedDeliveryBooking(): void
    {
        // =========================================================
        // 1. جلب أحمد
        // =========================================================

        $ahmad = User::find(3);

        if (!$ahmad) {
            $this->command->error(
                'Ahmed user with ID 3 was not found.'
            );

            return;
        }

        if ($ahmad->email !== 'ahmad@host.com') {
            $this->command->warn(
                "User ID 3 email is {$ahmad->email}, not ahmad@host.com."
            );
        }

        // =========================================================
        // التأكد أن أحمد Guest
        // =========================================================

        if (!$ahmad->hasRole('guest')) {
            $ahmad->assignRole('guest');

            $this->command->info(
                'Guest role assigned to Ahmed.'
            );
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
                'Host record for Ahmed was not found.'
            );

            return;
        }

        // =========================================================
        // 3. جلب سيارة لأحمد تدعم التوصيل
        //
        // نأخذ فقط:
        // listed
        // approved
        // delivery_available = true
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
            ->where(
                'delivery_available',
                true
            )
            ->first();

        // =========================================================
        // إذا لم توجد سيارة تدعم التوصيل
        // نبحث عن أي سيارة لأحمد
        // =========================================================

        if (!$vehicle) {
            $this->command->warn(
                'No delivery-enabled vehicle found for Ahmed.'
            );

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
        }

        if (!$vehicle) {
            $this->command->error(
                'No approved listed vehicle found for Ahmed.'
            );

            return;
        }

        // =========================================================
        // 4. تاريخ الحجز
        //
        // يبدأ اليوم
        // وينتهي بعد 5 أيام
        // =========================================================

        $startDate = now()->startOfDay();

        $endDate = now()
            ->startOfDay()
            ->addDays(5);

        // =========================================================
        // 5. التأكد من عدم وجود حجز متداخل
        //
        // نبحث عن:
        // confirmed
        // active
        //
        // لنفس السيارة ونفس الفترة
        // =========================================================

        $existingBooking = Booking::where(
            'vehicle_id',
            $vehicle->id
        )
            ->whereIn(
                'status',
                [
                    'confirmed',
                    'active',
                ]
            )
            ->whereDate(
                'start_date',
                '<=',
                $endDate->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $startDate->toDateString()
            )
            ->first();

        // =========================================================
        // إذا السيارة محجوزة
        //
        // نحاول إيجاد سيارة ثانية لأحمد
        // =========================================================

        if ($existingBooking) {

            $this->command->warn(
                "Vehicle #{$vehicle->id} already has a confirmed/active booking."
            );

            $availableVehicle = Vehicle::where(
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
                ->when(
                    $vehicle->delivery_available,
                    function ($query) {
                        $query->where(
                            'delivery_available',
                            true
                        );
                    }
                )
                ->whereDoesntHave(
                    'bookings',
                    function ($query) use (
                        $startDate,
                        $endDate
                    ) {
                        $query
                            ->whereIn(
                                'status',
                                [
                                    'confirmed',
                                    'active',
                                ]
                            )
                            ->whereDate(
                                'start_date',
                                '<=',
                                $endDate->toDateString()
                            )
                            ->whereDate(
                                'end_date',
                                '>=',
                                $startDate->toDateString()
                            );
                    }
                )
                ->first();

            if ($availableVehicle) {
                $vehicle = $availableVehicle;

                $this->command->info(
                    "Using available Vehicle #{$vehicle->id} instead."
                );
            } else {
                $this->command->error(
                    'No available vehicle found for Ahmed for the requested dates.'
                );

                return;
            }
        }

        // =========================================================
        // 6. إنشاء الحجز
        //
        // مهم جداً:
        //
        // user_id = Ahmed ID 3
        //
        // وليس guest@test.com
        // =========================================================

        $booking = $this->createAhmedDeliveryBooking(
            vehicle: $vehicle,
            guest: $ahmad,
            startDate: $startDate,
            endDate: $endDate,
        );

        // =========================================================
        // 7. عرض المعلومات
        // =========================================================

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            '🚗 AHMED CONFIRMED DELIVERY BOOKING'
        );

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            "User ID: {$ahmad->id}"
        );

        $this->command->info(
            "User Email: {$ahmad->email}"
        );

        $this->command->info(
            "User Role: guest"
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
            "Delivery Type: {$booking->delivery_type}"
        );

        $this->command->info(
            "Delivery Address: {$booking->delivery_address}"
        );

        $this->command->info(
            "Delivery Lat: {$booking->delivery_lat}"
        );

        $this->command->info(
            "Delivery Lng: {$booking->delivery_lng}"
        );

        $this->command->info(
            "Payment: paid"
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
    // CREATE AHMED DELIVERY BOOKING
    // =============================================================

    private function createAhmedDeliveryBooking(
        Vehicle $vehicle,
        User $guest,
        $startDate,
        $endDate,
    ): Booking {

        // =========================================================
        // TOTAL DAYS
        // =========================================================

        $totalDays = max(
            1,
            $startDate->diffInDays($endDate)
        );

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
        //
        // إجبارياً delivery
        // =========================================================

        $deliveryType = 'delivery';

        $deliveryFee = 10;

        // =========================================================
        // DELIVERY LOCATION
        // =========================================================

        $deliveryAddress =
            'Street 5, Damascus, Syria';

        $deliveryLat = 33.5138;

        $deliveryLng = 36.2765;

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
        //
        // أهم سطر:
        //
        // user_id = $guest->id
        //
        // وهنا $guest هو أحمد ID 3
        // =========================================================

        $booking = Booking::create([
            'vehicle_id' =>
                $vehicle->id,

            'host_id' =>
                $vehicle->host_id,

            'user_id' =>
                $guest->id,

            'start_date' =>
                $startDate->toDateString(),

            'end_date' =>
                $endDate->toDateString(),

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

            // مهم جداً
            'delivery_type' =>
                $deliveryType,

            // مهم جداً
            'delivery_address' =>
                $deliveryAddress,

            // Tracking / Map
            'delivery_lat' =>
                $deliveryLat,

            'delivery_lng' =>
                $deliveryLng,

            // مهم جداً
            'status' =>
                'confirmed',

            'cancellation_reason' =>
                null,

            'cancelled_by' =>
                null,
        ]);

        // =========================================================
        // PAYMENT
        //
        // الحجز confirmed
        // والدفع paid
        // =========================================================

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

        return $booking;
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
