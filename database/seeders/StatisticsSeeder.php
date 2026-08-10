<?php
// database/seeders/StatisticsSeeder.php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\Host;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StatisticsSeeder extends Seeder
{
    public function run(): void
    {
        $guestRole = Role::where('name', 'guest')->first();
        $hostRole  = Role::where('name', 'host')->first();

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 1. إنشاء 20 غيست
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        $guests = collect();
        foreach (range(1, 20) as $i) {
            $user = User::create([
                'full_name'         => "Guest User {$i}",
                'email'             => "guest{$i}@test.com",
                'password'          => Hash::make('password123'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now()->subDays(rand(1, 180)),
                'created_at'        => now()->subDays(rand(1, 180)),
            ]);
            $user->assignRole($guestRole);
            $guests->push($user);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 2. إنشاء حجوزات موزعة على 12 شهر
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        $vehicles = Vehicle::where('admin_review_status', 'approved')
                           ->where('listing_status', 'listed')
                           ->get();

        if ($vehicles->isEmpty()) {
            $this->command->warn('No approved vehicles found!');
            return;
        }

        $statuses = ['completed', 'completed', 'completed', 'cancelled', 'confirmed'];

        foreach (range(1, 12) as $monthsAgo) {
            $bookingsThisMonth = rand(8, 20);

            foreach (range(1, $bookingsThisMonth) as $b) {
                $vehicle    = $vehicles->random();
                $guest      = $guests->random();
                $totalDays  = rand(1, 7);
                $startDate  = now()->subMonths($monthsAgo)->addDays(rand(1, 20));
                $endDate    = $startDate->copy()->addDays($totalDays);
                $status     = $statuses[array_rand($statuses)];
                $subtotal   = round($vehicle->base_price_per_day * $totalDays, 2);
                $platformFee= round($subtotal * 0.10, 2);
                $totalAmount= $subtotal + $platformFee;

                $booking = Booking::create([
                    'vehicle_id'         => $vehicle->id,
                    'host_id'            => $vehicle->host_id,
                    'user_id'            => $guest->id,
                    'start_date'         => $startDate->toDateString(),
                    'end_date'           => $endDate->toDateString(),
                    'total_days'         => $totalDays,
                    'base_price_per_day' => $vehicle->base_price_per_day,
                    'subtotal'           => $subtotal,
                    'discount_amount'    => 0,
                    'delivery_fee'       => 0,
                    'platform_fee'       => $platformFee,
                    'total_amount'       => $totalAmount,
                    'delivery_type'      => 'pickup',
                    'status'             => $status,
                    'created_at'         => $startDate,
                    'updated_at'         => $startDate,
                ]);

                // ── Payment ───────────────────────────────────
                Payment::create([
                    'booking_id'  => $booking->id,
                    'payment_id'  => 'PAY-' . uniqid(),
                    'amount'      => $totalAmount,
                    'status'      => $status === 'cancelled' ? 'cancelled' : 'paid',
                    'paid_at'     => $status !== 'cancelled' ? $startDate : null,
                    'created_at'  => $startDate,
                    'updated_at'  => $startDate,
                ]);

                // ── Events للحجوزات المكتملة ──────────────────
                if ($status === 'completed') {
                    $events = ['guest_picked_up', 'guest_returned', 'host_received'];
                    

                    // ── تحديث إحصائيات الهوست ─────────────────
                    $netAmount = $totalAmount - $platformFee;
                    $vehicle->host->increment('total_earnings', $netAmount);
                    $vehicle->host->increment('available_balance', $netAmount);
                    $vehicle->host->increment('total_trips');

                    // ── تحديث إحصائيات السيارة ────────────────
                    $vehicle->increment('total_bookings');
                }
            }
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 3. تحديث rating_avg للسيارات
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        Vehicle::where('admin_review_status', 'approved')->each(function ($vehicle) {
            $vehicle->update([
                'rating_avg' => round(rand(38, 50) / 10, 1),
            ]);
        });

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 4. تحديث rating_avg للهوست
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        Host::each(function ($host) {
            $host->update([
                'rating_avg' => round(rand(40, 50) / 10, 1),
            ]);
        });

        $this->command->info('✅ Statistics seeder completed!');
        $this->command->info('   → 20 guests created');
        $this->command->info('   → Bookings distributed over 12 months');
        $this->command->info('   → Host earnings updated');
        $this->command->info('   → Vehicle ratings updated');
    }
}
