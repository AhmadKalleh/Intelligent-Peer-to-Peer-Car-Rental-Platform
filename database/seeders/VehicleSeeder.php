<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Host;
use App\Models\Vehicle;
use App\Models\VehicleFeature;
use App\Models\VehicleAvailability;
use App\Models\VehicleCustomPricing;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Feature;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء الحسابات والمضيفين بنسب تقييم عالية لمحاكاة خوارزمية الأفضلية و All-Star Host
        $hostsData = [
            ['name' => 'Ahmad Al-Kalleh', 'email' => 'ahmad@host.com', 'rating' => 4.95, 'trips' => 50],
            
        ];


        // 2. قاعدة بيانات سيارات موسعة تغطي الأقسام الجغرافية بدقة مذهلة
        $carsPool = [
            // دمشق - كفر سوسة والتوصيل
            ['make' => 'Lamborghini', 'model' => 'Urus', 'year' => 2024, 'city' => 'Damascus', 'price' => 600.00, 'address' => 'Syria, Damascus, Kafr Souseh Downtown', 'delivery' => false, 'lat' => 33.5000, 'lng' => 36.2700],
            ['make' => 'Tesla', 'model' => 'Model Y', 'year' => 2023, 'city' => 'Damascus', 'price' => 250.00, 'address' => 'Syria, Damascus, Kafr Souseh Park', 'delivery' => true, 'lat' => 33.5010, 'lng' => 36.2720],

            // دمشق - مطار دمشق الدولي
            ['make' => 'Dodge', 'model' => 'Durango', 'year' => 2024, 'city' => 'Damascus', 'price' => 120.00, 'address' => 'Syria, Damascus International Airport Arrival Gate', 'delivery' => false, 'lat' => 33.4110, 'lng' => 36.5150],
            ['make' => 'Mercedes', 'model' => 'S-Class', 'year' => 2023, 'city' => 'Damascus', 'price' => 400.00, 'address' => 'Syria, Damascus International Airport VIP Section', 'delivery' => false, 'lat' => 33.4120, 'lng' => 36.5160],

            // حمص - الإنشاءات والتوصيل
            ['make' => 'Toyota', 'model' => 'Camry', 'year' => 2023, 'city' => 'Homs', 'price' => 90.00, 'address' => 'Syria, Homs, Al-Inshaat Street', 'delivery' => true, 'lat' => 34.7200, 'lng' => 36.7000],
            ['make' => 'Kia', 'model' => 'Sportage', 'year' => 2022, 'city' => 'Homs', 'price' => 110.00, 'address' => 'Syria, Homs, Al-Inshaat Garden', 'delivery' => false, 'lat' => 34.7210, 'lng' => 36.7020],

            // حلب - الشهباء والتوصيل
            ['make' => 'BMW', 'model' => 'X5', 'year' => 2024, 'city' => 'Aleppo', 'price' => 300.00, 'address' => 'Syria, Aleppo, Shahbaa District Near Hotel', 'delivery' => false, 'lat' => 36.2200, 'lng' => 37.1200],
            ['make' => 'Audi', 'model' => 'A6', 'year' => 2023, 'city' => 'Aleppo', 'price' => 170.00, 'address' => 'Syria, Aleppo, Shahbaa Square', 'delivery' => false, 'lat' => 36.2210, 'lng' => 37.1220],
        ];

        $guestRole = Role::query()->where('name', '=', 'guest')->first();
        $hostRole = Role::query()->where('name', '=', 'host')->first();

        foreach ($hostsData as $i => $hData) {
            $user = User::create([
                'full_name'         => $hData['name'],
                'email'             => $hData['email'],
                'password'          => Hash::make('password'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole($guestRole);
            $user->assignRole($hostRole);
            $user->givePermissionTo(array_merge($guestRole->permissions->pluck('name')->toArray(), $hostRole->permissions->pluck('name')->toArray()));


            $host = Host::create([
                'user_id'            => $user->id,
                'total_earnings'     => 5000.00,
                'available_balance'  => 1500.00,
                'rating_avg'         => $hData['rating'],
                'total_trips'        => $hData['trips'],
                'delivery_available' => true,
                'delivery_fee_per_km'=> 5.00,
                'is_verified'        => true,
                'verified_at'        => now(),
            ]);

            // توليد وتكرار السيارات لتضخيم البيانات وضمان تعبئة الـ 8 سيارات لكل قسم
            foreach ($carsPool as $cIndex => $car) {
                // تكرار الحقن لإنشاء وفرة في الداتا بيز
                for ($instance = 1; $instance <= 2; $instance++) {
                    $vehicle = Vehicle::create([
                        'host_id'               => $host->id,
                        'make'                  => $car['make'],
                        'model'                 => $car['model'] . " ($instance)",
                        'year'                  => $car['year'],
                        'color'                 => 'White Metallic',
                        'fuel_type'             => 'petrol',
                        'transmission'          => 'automatic',
                        'engine_capacity'       => 2.4,
                        'seats'                 => 5,
                        'plate_number'          => 'Syria-' . rand(100000, 999999) . "-$cIndex-$instance",
                        'listing_status'        => 'listed',
                        'admin_review_status'   => 'approved',
                        'reviewed_at'           => now(),
                        'base_price_per_day'    => $car['price'],
                        'delivery_available'    => $car['delivery'],
                        'delivery_fee'          => $car['delivery'] ? 20.00 : null,
                        'pickup_address'        => $car['address'],
                        'pickup_lat'            => $car['lat'] + (rand(-10, 10) / 1000), // تغيير طفيف للإحداثيات لخدمة الـ Nearby
                        'pickup_lng'            => $car['lng'] + (rand(-10, 10) / 1000),
                        'city'                  => $car['city'],
                        'total_reviews'         => rand(20, 80),
                        'total_bookings'        => rand(30, 100),
                        'rating_avg'            => 4.6 + (rand(1, 3) * 0.1),
                    ]);

                    // إضافة ميزات وصور بوليمورفيك
                    $featureIds = Feature::inRandomOrder()->limit(3)->pluck('id')->toArray();
                    $vehicle->features()->sync($featureIds);

                    VehicleAvailability::create([
                        'vehicle_id' => $vehicle->id, 'available_from' => now()->toDateString(), 'available_to' => now()->addMonths(6)->toDateString()
                    ]);

                    if($cIndex %2 ==0)
                    {
                        VehicleCustomPricing::create([
                            'vehicle_id'    => $vehicle->id,
                            'date_from'     => now()->subDays(20)->toDateString(),
                            'date_to'       => now()->subDays(10)->toDateString(),
                            'price_per_day' => $vehicle->base_price_per_day * 1.3,
                            'reason'        => 'Easter Holiday - Expired',
                        ]);

                        // ─── 2. فترة نشطة الآن (active) ──────────────────
                        VehicleCustomPricing::create([
                            'vehicle_id'    => $vehicle->id,
                            'date_from'     => now()->subDays(2)->toDateString(),
                            'date_to'       => now()->addDays(5)->toDateString(),
                            'price_per_day' => $vehicle->base_price_per_day * 1.5,
                            'reason'        => 'Weekend Special - Active Now',
                        ]);

                        // ─── 3. فترة قادمة أولى (upcoming) ───────────────
                        VehicleCustomPricing::create([
                            'vehicle_id'    => $vehicle->id,
                            'date_from'     => now()->addDays(10)->toDateString(),
                            'date_to'       => now()->addDays(15)->toDateString(),
                            'price_per_day' => $vehicle->base_price_per_day * 1.8,
                            'reason'        => 'Eid Holiday - Upcoming',
                        ]);

                        // ─── 4. فترة قادمة ثانية (upcoming) ──────────────
                        VehicleCustomPricing::create([
                            'vehicle_id'    => $vehicle->id,
                            'date_from'     => now()->addDays(20)->toDateString(),
                            'date_to'       => now()->addDays(25)->toDateString(),
                            'price_per_day' => $vehicle->base_price_per_day * 2.0,
                            'reason'        => 'Summer Peak - Upcoming',
                        ]);
                    }

                    Image::create(['imageable_type' => Vehicle::class, 'imageable_id' => $vehicle->id, 'hash' =>'1234', 'path' => "vehicles/Audi-E-Tron.png", 'sort_order' => 0, 'is_primary' => true]);
                }
            }
        }
    }
}
