<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Host;
use App\Models\Image;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAvailability;
use App\Models\VehicleCustomPricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $guestRole = Role::query()
            ->where('name', 'guest')
            ->firstOrFail();

        $hostRole = Role::query()
            ->where('name', 'host')
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Hosts
        |--------------------------------------------------------------------------
        */

        $hostsData = [
            [
                'name'   => 'Ahmad Al-Kalleh',
                'email'  => 'ahmad@host.com',
                'rating' => 4.95,
                'trips'  => 50,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Vehicles
        |--------------------------------------------------------------------------
        |
        | Each vehicle has its own real model information and its own image.
        | The image filename must match the actual file in storage.
        |
        */

        $vehiclesData = [

            // ================================================================
            // AUDI
            // ================================================================

            [
                'make'              => 'Audi',
                'model'             => 'A3',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 85.00,
                'address'           => 'Syria, Damascus, Kafr Souseh',
                'delivery'          => true,
                'lat'               => 33.5000,
                'lng'               => 36.2700,
                'image'             => 'AudiA3.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 1.4,
                'seats'             => 5,
                'color'             => 'Black',
            ],

            [
                'make'              => 'Audi',
                'model'             => 'A6',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 170.00,
                'address'           => 'Syria, Damascus, Al-Malki',
                'delivery'          => true,
                'lat'               => 33.5150,
                'lng'               => 36.2850,
                'image'             => 'AudiA6.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 5,
                'color'             => 'White',
            ],

            [
                'make'              => 'Audi',
                'model'             => 'S4',
                'year'              => 2022,
                'city'              => 'Aleppo',
                'price'             => 150.00,
                'address'           => 'Syria, Aleppo, Shahbaa',
                'delivery'          => false,
                'lat'               => 36.2200,
                'lng'               => 37.1200,
                'image'             => 'AudiS4.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 3.0,
                'seats'             => 5,
                'color'             => 'Grey',
            ],

            [
                'make'              => 'Audi',
                'model'             => 'TT',
                'year'              => 2021,
                'city'              => 'Damascus',
                'price'             => 140.00,
                'address'           => 'Syria, Damascus, Abu Rummaneh',
                'delivery'          => false,
                'lat'               => 33.5155,
                'lng'               => 36.2950,
                'image'             => 'AudiTT.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 2,
                'color'             => 'Red',
            ],

            [
                'make'              => 'Audi',
                'model'             => 'A7',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 220.00,
                'address'           => 'Syria, Damascus, Mezzeh',
                'delivery'          => true,
                'lat'               => 33.4950,
                'lng'               => 36.2450,
                'image'             => 'Audi-A7.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 5,
                'color'             => 'Dark Blue',
            ],

            // ================================================================
            // BMW
            // ================================================================

            [
                'make'              => 'BMW',
                'model'             => '2 Series',
                'year'              => 2022,
                'city'              => 'Damascus',
                'price'             => 120.00,
                'address'           => 'Syria, Damascus, Mazzeh',
                'delivery'          => true,
                'lat'               => 33.4900,
                'lng'               => 36.2500,
                'image'             => 'BMW(2).png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 5,
                'color'             => 'White',
            ],

            [
                'make'              => 'BMW',
                'model'             => '3 Series',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 135.00,
                'address'           => 'Syria, Damascus, Kafr Souseh',
                'delivery'          => true,
                'lat'               => 33.5005,
                'lng'               => 36.2710,
                'image'             => 'BMW(3).png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 5,
                'color'             => 'Black',
            ],

            [
                'make'              => 'BMW',
                'model'             => '6 Series',
                'year'              => 2021,
                'city'              => 'Aleppo',
                'price'             => 190.00,
                'address'           => 'Syria, Aleppo, Shahbaa',
                'delivery'          => false,
                'lat'               => 36.2205,
                'lng'               => 37.1210,
                'image'             => 'BMW(6).png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 3.0,
                'seats'             => 5,
                'color'             => 'Grey',
            ],

            [
                'make'              => 'BMW',
                'model'             => '7 Series',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 280.00,
                'address'           => 'Syria, Damascus, Abu Rummaneh',
                'delivery'          => true,
                'lat'               => 33.5160,
                'lng'               => 36.2960,
                'image'             => 'BMW(7).png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 3.0,
                'seats'             => 5,
                'color'             => 'Black',
            ],

            [
                'make'              => 'BMW',
                'model'             => '8 Series',
                'year'              => 2022,
                'city'              => 'Damascus',
                'price'             => 300.00,
                'address'           => 'Syria, Damascus, Mezzeh',
                'delivery'          => false,
                'lat'               => 33.4955,
                'lng'               => 36.2460,
                'image'             => 'BMW(8).png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 3.0,
                'seats'             => 4,
                'color'             => 'Dark Blue',
            ],

            // ================================================================
            // MITSUBISHI
            // ================================================================

            [
                'make'              => 'Mitsubishi',
                'model'             => 'Delica',
                'year'              => 2021,
                'city'              => 'As-swaida',
                'price'             => 100.00,
                'address'           => 'Syria, As-swaida , Al-Omran',
                'delivery'          => true,
                'lat'               => 34.7200,
                'lng'               => 36.7000,
                'image'             => 'Mus-Delica.png',
                'fuel_type'         => 'diesel',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.3,
                'seats'             => 8,
                'color'             => 'White',
            ],

            [
                'make'              => 'Mitsubishi',
                'model'             => 'Eclipse Cross',
                'year'              => 2022,
                'city'              => 'Homs',
                'price'             => 95.00,
                'address'           => 'Syria, Homs, Al-Inshaat',
                'delivery'          => true,
                'lat'               => 34.7210,
                'lng'               => 36.7020,
                'image'             => 'Mus-Ele.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 1.5,
                'seats'             => 5,
                'color'             => 'Red',
            ],

            [
                'make'              => 'Mitsubishi',
                'model'             => 'Lancer',
                'year'              => 2020,
                'city'               => 'Homs',
                'price'             => 65.00,
                'address'           => 'Syria, Homs, Al-Inshaat',
                'delivery'          => false,
                'lat'               => 34.7220,
                'lng'               => 36.7030,
                'image'             => 'Mus-Lancer.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 1.6,
                'seats'             => 5,
                'color'             => 'Silver',
            ],

            [
                'make'              => 'Mitsubishi',
                'model'             => 'Outlander Sport',
                'year'              => 2022,
                'city'              => 'Homs',
                'price'             => 90.00,
                'address'           => 'Syria, Homs, Al-Waer',
                'delivery'          => true,
                'lat'               => 34.7350,
                'lng'               => 36.6900,
                'image'             => 'Mus-Sport.png',
                'fuel_type'         => 'petrol',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.0,
                'seats'             => 5,
                'color'             => 'Grey',
            ],

            [
                'make'              => 'Mitsubishi',
                'model'             => 'Triton',
                'year'              => 2023,
                'city'              => 'Homs',
                'price'             => 110.00,
                'address'           => 'Syria, Homs, Industrial Area',
                'delivery'          => true,
                'lat'               => 34.7400,
                'lng'               => 36.7100,
                'image'             => 'Mus-Triton.png',
                'fuel_type'         => 'diesel',
                'transmission'      => 'automatic',
                'engine_capacity'   => 2.4,
                'seats'             => 5,
                'color'             => 'Black',
            ],

            // ================================================================
            // TESLA
            // ================================================================

            [
                'make'              => 'Tesla',
                'model'             => 'Model 3',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 180.00,
                'address'           => 'Syria, Damascus, Kafr Souseh',
                'delivery'          => true,
                'lat'               => 33.5010,
                'lng'               => 36.2720,
                'image'             => 'tesla1.png',
                'fuel_type'         => 'electric',
                'transmission'      => 'automatic',
                'engine_capacity'   => null,
                'seats'             => 5,
                'color'             => 'White',
            ],

            [
                'make'              => 'Tesla',
                'model'             => 'Model Y',
                'year'              => 2023,
                'city'              => 'Damascus',
                'price'             => 200.00,
                'address'           => 'Syria, Damascus, Kafr Souseh Park',
                'delivery'          => true,
                'lat'               => 33.5020,
                'lng'               => 36.2730,
                'image'             => 'tesla2.png',
                'fuel_type'         => 'electric',
                'transmission'      => 'automatic',
                'engine_capacity'   => null,
                'seats'             => 5,
                'color'             => 'Black',
            ],

            [
                'make'              => 'Tesla',
                'model'             => 'Model S',
                'year'              => 2022,
                'city'              => 'Damascus',
                'price'             => 280.00,
                'address'           => 'Syria, Damascus, Al-Malki',
                'delivery'          => false,
                'lat'               => 33.5150,
                'lng'               => 36.2850,
                'image'             => 'tesls.png',
                'fuel_type'         => 'electric',
                'transmission'      => 'automatic',
                'engine_capacity'   => null,
                'seats'             => 5,
                'color'             => 'Red',
            ],

            [
                'make'              => 'Tesla',
                'model'             => 'Model X',
                'year'              => 2022,
                'city'              => 'Aleppo',
                'price'             => 320.00,
                'address'           => 'Syria, Aleppo, Shahbaa',
                'delivery'          => true,
                'lat'               => 36.2210,
                'lng'               => 37.1220,
                'image'             => 'tesls3.png',
                'fuel_type'         => 'electric',
                'transmission'      => 'automatic',
                'engine_capacity'   => null,
                'seats'             => 7,
                'color'             => 'White',
            ],

            [
                'make'              => 'Tesla',
                'model'             => 'Model 3 Long Range',
                'year'              => 2024,
                'city'              => 'Damascus',
                'price'             => 220.00,
                'address'           => 'Syria, Damascus International Airport',
                'delivery'          => true,
                'lat'               => 33.4110,
                'lng'               => 36.5150,
                'image'             => 'tesls4.png',
                'fuel_type'         => 'electric',
                'transmission'      => 'automatic',
                'engine_capacity'   => null,
                'seats'             => 5,
                'color'             => 'Grey',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Hosts + Vehicles
        |--------------------------------------------------------------------------
        */

        foreach ($hostsData as $hostData) {

            /*
             * Create user
             */
            $user = User::create([
                'full_name'         => $hostData['name'],
                'email'             => $hostData['email'],
                'password'          => Hash::make('password'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            /*
             * Assign roles
             */
            $user->assignRole([
                $guestRole,
                $hostRole,
            ]);

            /*
             * Give combined permissions
             */
            $permissions = $guestRole->permissions
                ->merge($hostRole->permissions)
                ->pluck('name')
                ->unique()
                ->values()
                ->toArray();

            $user->givePermissionTo($permissions);

            /*
             * Create host profile
             */
            $host = Host::create([
                'user_id'             => $user->id,
                'total_earnings'      => 5000.00,
                'available_balance'   => 1500.00,
                'rating_avg'         => $hostData['rating'],
                'total_trips'        => $hostData['trips'],
                'delivery_available' => true,
                'delivery_fee_per_km'=> 5.00,
                'is_verified'        => true,
                'verified_at'        => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Vehicles
            |--------------------------------------------------------------------------
            */

            foreach ($vehiclesData as $index => $vehicleData) {

                $vehicle = Vehicle::create([
                    'host_id'             => $host->id,
                    'make'                => $vehicleData['make'],
                    'model'               => $vehicleData['model'],
                    'year'                => $vehicleData['year'],
                    'color'               => $vehicleData['color'],
                    'fuel_type'           => $vehicleData['fuel_type'],
                    'transmission'        => $vehicleData['transmission'],
                    'engine_capacity'     => $vehicleData['engine_capacity'],
                    'seats'               => $vehicleData['seats'],

                    'plate_number'        => sprintf(
                        'SY-%03d-%04d',
                        $host->id,
                        $index + 1
                    ),

                    'listing_status'      => 'listed',
                    'admin_review_status' => 'approved',
                    'reviewed_at'         => now(),

                    'base_price_per_day'  => $vehicleData['price'],

                    'delivery_available'  => $vehicleData['delivery'],
                    'delivery_fee'        => $vehicleData['delivery']
                        ? 20.00
                        : null,

                    'pickup_address'      => $vehicleData['address'],

                    'pickup_lat'          => $vehicleData['lat'],
                    'pickup_lng'          => $vehicleData['lng'],

                    'city'                => $vehicleData['city'],

                    'guest_instructions'  =>
                        'Please keep the vehicle clean and return it with the same fuel level.',

                    'total_reviews'       => rand(20, 80),
                    'total_bookings'      => rand(30, 100),
                    'rating_avg'          => 4.6 + (rand(1, 3) * 0.1),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Vehicle Features
                |--------------------------------------------------------------------------
                */

                $featureIds = Feature::query()
                    ->inRandomOrder()
                    ->limit(3)
                    ->pluck('id')
                    ->toArray();

                if (!empty($featureIds)) {
                    $vehicle->features()->sync($featureIds);
                }

                /*
                |--------------------------------------------------------------------------
                | Vehicle Availability
                |--------------------------------------------------------------------------
                */

                VehicleAvailability::create([
                    'vehicle_id'    => $vehicle->id,
                    'available_from' => now()->toDateString(),
                    'available_to'   => now()->addMonths(6)->toDateString(),
                    'is_blocked'     => false,
                    'type'           => 'available',
                    'blocked_by'     => null,
                    'block_reason'   => null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Custom Pricing
                |--------------------------------------------------------------------------
                */

                // Expired pricing
                VehicleCustomPricing::create([
                    'vehicle_id'    => $vehicle->id,
                    'date_from'     => now()->subDays(20)->toDateString(),
                    'date_to'       => now()->subDays(10)->toDateString(),
                    'price_per_day' => round(
                        $vehicle->base_price_per_day * 1.30,
                        2
                    ),
                    'reason'        => 'Holiday Special - Expired',
                ]);

                // Active pricing
                VehicleCustomPricing::create([
                    'vehicle_id'    => $vehicle->id,
                    'date_from'     => now()->subDays(2)->toDateString(),
                    'date_to'       => now()->addDays(5)->toDateString(),
                    'price_per_day' => round(
                        $vehicle->base_price_per_day * 1.50,
                        2
                    ),
                    'reason'        => 'Weekend Special - Active',
                ]);

                // Upcoming pricing #1
                VehicleCustomPricing::create([
                    'vehicle_id'    => $vehicle->id,
                    'date_from'     => now()->addDays(10)->toDateString(),
                    'date_to'       => now()->addDays(15)->toDateString(),
                    'price_per_day' => round(
                        $vehicle->base_price_per_day * 1.80,
                        2
                    ),
                    'reason'        => 'Holiday Season - Upcoming',
                ]);

                // Upcoming pricing #2
                VehicleCustomPricing::create([
                    'vehicle_id'    => $vehicle->id,
                    'date_from'     => now()->addDays(20)->toDateString(),
                    'date_to'       => now()->addDays(25)->toDateString(),
                    'price_per_day' => round(
                        $vehicle->base_price_per_day * 2.00,
                        2
                    ),
                    'reason'        => 'Summer Peak - Upcoming',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Vehicle Image
                |--------------------------------------------------------------------------
                */

                Image::create([
                    'imageable_type' => Vehicle::class,
                    'imageable_id'   => $vehicle->id,
                    'path'           => 'vehicles/' . $vehicleData['image'],
                    'sort_order'     => 0,
                    'is_primary'     => true,
                    'type'           => 'vehicle_image',
                    'hash'           => md5(
                        $vehicleData['image'] . '-' . $vehicle->id
                    ),
                ]);
            }
        }
    }
}
