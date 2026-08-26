<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Host;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class StatisticsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('🚀 STARTING STATISTICS SEEDER');
        $this->command->info('==============================================');
        $this->command->info('');

        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */

        $numberOfGuests = 40;

        $monthsToGenerate = 12;

        $minBookingsPerMonth = 20;
        $maxBookingsPerMonth = 35;

        $platformFeePercentage = 0.10;

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $guestRole = Role::where('name', 'guest')->first();

        if (!$guestRole) {
            $this->command->error(
                '❌ Guest role does not exist.'
            );

            $this->command->warn(
                'Please create the guest role first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | GUEST NAMES
        |--------------------------------------------------------------------------
        */

        $guestNames = [
            'Ahmad Khaled',
            'Mohammad Ali',
            'Omar Hassan',
            'Yousef Ahmad',
            'Khaled Mahmoud',
            'Omar Khalil',
            'Yazan Saleh',
            'Tareq Hamdan',
            'Zaid Nasser',
            'Samer Ibrahim',

            'Lina Ahmad',
            'Sara Khaled',
            'Nour Hassan',
            'Maya Saleh',
            'Rana Mahmoud',
            'Dana Khalil',
            'Hala Nasser',
            'Lama Ibrahim',
            'Jana Hamdan',
            'Rima Ali',

            'Adam George',
            'Daniel Smith',
            'James Wilson',
            'Michael Brown',
            'David Miller',
            'John Anderson',
            'Robert Taylor',
            'William Thomas',
            'Emma Johnson',
            'Sophia Williams',

            'Noah Davis',
            'Oliver Martin',
            'Lucas Thompson',
            'Henry White',
            'Jack Harris',
            'Emily Clark',
            'Charlotte Lewis',
            'Amelia Walker',
            'Grace Hall',
            'Ella Allen',
        ];

        /*
        |--------------------------------------------------------------------------
        | CREATE / UPDATE GUESTS
        |--------------------------------------------------------------------------
        */

        $this->command->info('👤 Creating guests...');

        $guests = collect();

        for ($i = 1; $i <= $numberOfGuests; $i++) {

            $name = $guestNames[$i - 1] ?? "Guest {$i}";

            $email = "statistics.guest{$i}@test.com";

            $verifiedAt = now()
                ->copy()
                ->subDays(rand(1, 365));

            $guest = User::updateOrCreate(
                [
                    'email' => $email,
                ],
                [
                    'full_name' => $name,
                    'password' => Hash::make('password123'),
                    'auth_provider' => 'local',
                    'status' => 'active',
                    'email_verified_at' => $verifiedAt,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | ASSIGN GUEST ROLE
            |--------------------------------------------------------------------------
            */

            if (!$guest->hasRole('guest')) {
                $guest->assignRole($guestRole);
            }

            $guests->push($guest);
        }

        $this->command->info(
            "   ✅ {$guests->count()} guests ready."
        );

        /*
        |--------------------------------------------------------------------------
        | LOAD HOSTS
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('👨‍💼 Loading hosts...');

        $hosts = Host::query()->get();

        if ($hosts->isEmpty()) {

            $this->command->error(
                '❌ No hosts found.'
            );

            $this->command->warn(
                'Create hosts before running StatisticsSeeder.'
            );

            return;
        }

        $this->command->info(
            "   ✅ {$hosts->count()} hosts found."
        );

        /*
        |--------------------------------------------------------------------------
        | LOAD VEHICLES
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('🚗 Loading vehicles...');

        /*
        |--------------------------------------------------------------------------
        | First: Approved + Listed
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->where('admin_review_status', 'approved')
            ->where('listing_status', 'listed')
            ->with('host')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Fallback: All Vehicles
        |--------------------------------------------------------------------------
        */

        if ($vehicles->isEmpty()) {

            $this->command->warn(
                '⚠️ No approved + listed vehicles found.'
            );

            $this->command->warn(
                '⚠️ Loading all existing vehicles instead...'
            );

            $vehicles = Vehicle::query()
                ->with('host')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | No Vehicles
        |--------------------------------------------------------------------------
        */

        if ($vehicles->isEmpty()) {

            $this->command->error(
                '❌ No vehicles exist in database.'
            );

            $this->command->warn(
                '❌ Cannot create bookings without vehicles.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Keep Vehicles With Valid Hosts
        |--------------------------------------------------------------------------
        */

        $vehicles = $vehicles
            ->filter(function ($vehicle) {

                return $vehicle->host_id !== null
                    && $vehicle->host !== null;
            })
            ->values();

        if ($vehicles->isEmpty()) {

            $this->command->error(
                '❌ Vehicles exist but none have a valid host.'
            );

            $this->command->warn(
                'Check vehicle.host_id and hosts table.'
            );

            return;
        }

        $this->command->info(
            "   ✅ {$vehicles->count()} usable vehicles found."
        );

        /*
        |--------------------------------------------------------------------------
        | BOOKING STATUS DISTRIBUTION
        |--------------------------------------------------------------------------
        */

        $statuses = [
            'completed',
            'completed',
            'completed',
            'completed',
            'completed',
            'completed',

            'confirmed',
            'confirmed',

            'active',

            'pending',

            'cancelled',
            'cancelled',
        ];

        /*
        |--------------------------------------------------------------------------
        | COUNTERS
        |--------------------------------------------------------------------------
        */

        $totalBookings = 0;

        $totalCompleted = 0;

        $totalConfirmed = 0;

        $totalCancelled = 0;

        $totalPaidPayments = 0;

        $totalCancelledPayments = 0;

        $totalRevenue = 0;

        $totalPlatformFees = 0;

        /*
        |--------------------------------------------------------------------------
        | GENERATE 12 MONTHS
        |--------------------------------------------------------------------------
        */

        $this->command->info('');

        $this->command->info(
            "📅 Generating {$monthsToGenerate} months of data..."
        );

        for (
            $monthsAgo = $monthsToGenerate - 1;
            $monthsAgo >= 0;
            $monthsAgo--
        ) {

            $bookingsThisMonth = rand(
                $minBookingsPerMonth,
                $maxBookingsPerMonth
            );

            $monthDate = now()
                ->copy()
                ->subMonths($monthsAgo);

            $monthName = $monthDate->format('Y-m');

            $this->command->info(
                "   📅 {$monthName}: {$bookingsThisMonth} bookings"
            );

            /*
            |--------------------------------------------------------------------------
            | BOOKINGS
            |--------------------------------------------------------------------------
            */

            for (
                $bookingNumber = 1;
                $bookingNumber <= $bookingsThisMonth;
                $bookingNumber++
            ) {

                /*
                |--------------------------------------------------------------------------
                | RANDOM VEHICLE
                |--------------------------------------------------------------------------
                */

                $vehicle = $vehicles->random();

                /*
                |--------------------------------------------------------------------------
                | RANDOM GUEST
                |--------------------------------------------------------------------------
                */

                $guest = $guests->random();

                /*
                |--------------------------------------------------------------------------
                | MONTH RANGE
                |--------------------------------------------------------------------------
                */

                $monthStart = $monthDate
                    ->copy()
                    ->startOfMonth();

                $monthEnd = $monthDate
                    ->copy()
                    ->endOfMonth();

                /*
                |--------------------------------------------------------------------------
                | START DATE
                |--------------------------------------------------------------------------
                */

                $lastPossibleDay = min(
                    20,
                    $monthEnd->day
                );

                $day = rand(
                    1,
                    max(1, $lastPossibleDay)
                );

                $startDate = Carbon::create(
                    $monthStart->year,
                    $monthStart->month,
                    $day,
                    rand(8, 18),
                    rand(0, 59),
                    0
                );

                /*
                |--------------------------------------------------------------------------
                | RENTAL DAYS
                |--------------------------------------------------------------------------
                */

                $totalDays = rand(1, 10);

                $endDate = $startDate
                    ->copy()
                    ->addDays($totalDays);

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                $statusIndex = (
                    $bookingNumber
                    + $monthsAgo
                    - 1
                ) % count($statuses);

                $status = $statuses[
                    $statusIndex
                ];

                /*
                |--------------------------------------------------------------------------
                | PRICE PER DAY
                |--------------------------------------------------------------------------
                */

                $basePricePerDay = (float)
                    $vehicle->base_price_per_day;

                if ($basePricePerDay <= 0) {

                    $basePricePerDay = rand(
                        35,
                        180
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | SUBTOTAL
                |--------------------------------------------------------------------------
                */

                $subtotal = round(
                    $basePricePerDay * $totalDays,
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT
                |--------------------------------------------------------------------------
                */

                $discountAmount = 0;

                if ($bookingNumber % 5 === 0) {

                    $discountAmount = round(
                        $subtotal *
                        (rand(5, 15) / 100),
                        2
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | DELIVERY FEE
                |--------------------------------------------------------------------------
                */

                $deliveryFee = 0;

                if (rand(1, 100) <= 25) {

                    $deliveryFee = rand(
                        10,
                        40
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | PLATFORM FEE
                |--------------------------------------------------------------------------
                */

                $platformFee = round(
                    $subtotal *
                    $platformFeePercentage,
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalAmount = round(
                    $subtotal
                    - $discountAmount
                    + $deliveryFee
                    + $platformFee,
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | DELIVERY TYPE
                |--------------------------------------------------------------------------
                */

                $deliveryType =
                    $deliveryFee > 0
                        ? 'delivery'
                        : 'pickup';

                /*
                |--------------------------------------------------------------------------
                | CREATE BOOKING
                |--------------------------------------------------------------------------
                */

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

                    'delivery_type' =>
                        $deliveryType,

                    'status' =>
                        $status,

                    'created_at' =>
                        $startDate,

                    'updated_at' =>
                        $startDate,
                ]);

                $totalBookings++;

                /*
                |--------------------------------------------------------------------------
                | STATUS COUNTERS
                |--------------------------------------------------------------------------
                */

                if ($status === 'completed') {

                    $totalCompleted++;
                }

                if ($status === 'confirmed') {

                    $totalConfirmed++;
                }

                if ($status === 'cancelled') {

                    $totalCancelled++;
                }

                /*
                |--------------------------------------------------------------------------
                | PAYMENT
                |--------------------------------------------------------------------------
                */

                $paymentStatus =
                    $status === 'cancelled'
                        ? 'cancelled'
                        : 'paid';

                Payment::create([

                    'booking_id' =>
                        $booking->id,

                    'payment_id' =>
                        'STAT-PAY-' .
                        strtoupper(
                            uniqid()
                        ),

                    'amount' =>
                        $totalAmount,

                    'status' =>
                        $paymentStatus,

                    'paid_at' =>
                        $paymentStatus === 'paid'
                            ? $startDate
                            : null,

                    'created_at' =>
                        $startDate,

                    'updated_at' =>
                        $startDate,
                ]);

                /*
                |--------------------------------------------------------------------------
                | PAYMENT COUNTERS
                |--------------------------------------------------------------------------
                */

                if ($paymentStatus === 'paid') {

                    $totalPaidPayments++;

                } else {

                    $totalCancelledPayments++;
                }

                /*
                |--------------------------------------------------------------------------
                | COMPLETED BOOKING
                |--------------------------------------------------------------------------
                */

                if ($status === 'completed') {

                    $netAmount = round(
                        $totalAmount -
                        $platformFee,
                        2
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | HOST
                    |--------------------------------------------------------------------------
                    */

                    $host = $vehicle->host;

                    if ($host) {

                        /*
                        | total_earnings
                        */

                        if (
                            $this->columnExists(
                                $host,
                                'total_earnings'
                            )
                        ) {

                            $host->increment(
                                'total_earnings',
                                $netAmount
                            );
                        }

                        /*
                        | available_balance
                        */

                        if (
                            $this->columnExists(
                                $host,
                                'available_balance'
                            )
                        ) {

                            $host->increment(
                                'available_balance',
                                $netAmount
                            );
                        }

                        /*
                        | total_trips
                        */

                        if (
                            $this->columnExists(
                                $host,
                                'total_trips'
                            )
                        ) {

                            $host->increment(
                                'total_trips'
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VEHICLE TOTAL BOOKINGS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->columnExists(
                            $vehicle,
                            'total_bookings'
                        )
                    ) {

                        $vehicle->increment(
                            'total_bookings'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | REVENUE
                    |--------------------------------------------------------------------------
                    */

                    $totalRevenue +=
                        $netAmount;

                    $totalPlatformFees +=
                        $platformFee;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE VEHICLE RATINGS
        |--------------------------------------------------------------------------
        */

        $this->command->info('');

        $this->command->info(
            '⭐ Updating vehicle ratings...'
        );

        Vehicle::query()->each(
            function ($vehicle) {

                if (
                    !$this->columnExists(
                        $vehicle,
                        'rating_avg'
                    )
                ) {
                    return;
                }

                $rating = round(
                    rand(38, 50) / 10,
                    1
                );

                $vehicle->update([
                    'rating_avg' => $rating,
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | UPDATE HOST RATINGS
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            '⭐ Updating host ratings...'
        );

        Host::query()->each(
            function ($host) {

                if (
                    !$this->columnExists(
                        $host,
                        'rating_avg'
                    )
                ) {
                    return;
                }

                $rating = round(
                    rand(40, 50) / 10,
                    1
                );

                $host->update([
                    'rating_avg' => $rating,
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | UPDATE GUEST BOOKING COUNTERS
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                (new User())->getTable(),
                'total_bookings'
            )
        ) {

            $this->command->info(
                '👥 Updating guest booking counters...'
            );

            foreach ($guests as $guest) {

                $bookingCount = Booking::where(
                    'user_id',
                    $guest->id
                )->count();

                $guest->update([
                    'total_bookings' =>
                        $bookingCount,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL SUMMARY
        |--------------------------------------------------------------------------
        */

        $this->command->newLine();

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            '🎉 STATISTICS SEEDER COMPLETED'
        );

        $this->command->info(
            '=============================================='
        );

        $this->command->info(
            "👤 Guests: {$guests->count()}"
        );

        $this->command->info(
            "👨‍💼 Hosts: {$hosts->count()}"
        );

        $this->command->info(
            "🚗 Vehicles used: {$vehicles->count()}"
        );

        $this->command->info(
            "📅 Total bookings: {$totalBookings}"
        );

        $this->command->info(
            "✅ Completed: {$totalCompleted}"
        );

        $this->command->info(
            "🟡 Confirmed: {$totalConfirmed}"
        );

        $this->command->info(
            "❌ Cancelled: {$totalCancelled}"
        );

        $this->command->info(
            "💳 Paid payments: {$totalPaidPayments}"
        );

        $this->command->info(
            "🚫 Cancelled payments: {$totalCancelledPayments}"
        );

        $this->command->info(
            '💰 Host revenue: ' .
            number_format(
                $totalRevenue,
                2
            )
        );

        $this->command->info(
            '🏦 Platform fees: ' .
            number_format(
                $totalPlatformFees,
                2
            )
        );

        $this->command->info(
            '⭐ Vehicle ratings: updated'
        );

        $this->command->info(
            '⭐ Host ratings: updated'
        );

        $this->command->info(
            '📊 Dashboard data: populated'
        );

        $this->command->info(
            '=============================================='
        );

        $this->command->newLine();
    }

    /**
     * Safely check if a model table contains a column.
     */
    private function columnExists(
        $model,
        string $column
    ): bool {
        try {

            return Schema::hasColumn(
                $model->getTable(),
                $column
            );

        } catch (\Throwable $e) {

            return false;
        }
    }
}
