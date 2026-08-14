<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Host;
use App\Models\Vehicle;
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
        /*
        |--------------------------------------------------------------------------
        | 1. HOST DATA
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
        | 2. EXISTING VEHICLES
        |--------------------------------------------------------------------------
        */

        $carsPool = [

            // Damascus - Kafr Souseh
            [
                'make'     => 'Lamborghini',
                'model'    => 'Urus',
                'year'     => 2024,
                'city'     => 'Damascus',
                'price'    => 600.00,
                'address'  => 'Syria, Damascus, Kafr Souseh Downtown',
                'delivery' => false,
                'lat'      => 33.5000,
                'lng'      => 36.2700,
                'image'    => null,
            ],

            [
                'make'     => 'Tesla',
                'model'    => 'Model Y',
                'year'     => 2023,
                'city'     => 'Damascus',
                'price'    => 250.00,
                'address'  => 'Syria, Damascus, Kafr Souseh Park',
                'delivery' => true,
                'lat'      => 33.5010,
                'lng'      => 36.2720,
                'image'    => 'vehicles/Tesla images/tesla-model-x.png',
            ],

            // Damascus International Airport
            [
                'make'     => 'Dodge',
                'model'    => 'Durango',
                'year'     => 2024,
                'city'     => 'Damascus',
                'price'    => 120.00,
                'address'  => 'Syria, Damascus International Airport Arrival Gate',
                'delivery' => false,
                'lat'      => 33.4110,
                'lng'      => 36.5150,
                'image'    => null,
            ],

            [
                'make'     => 'Mercedes',
                'model'    => 'S-Class',
                'year'     => 2023,
                'city'     => 'Damascus',
                'price'    => 400.00,
                'address'  => 'Syria, Damascus International Airport VIP Section',
                'delivery' => false,
                'lat'      => 33.4120,
                'lng'      => 36.5160,
                'image'    => null,
            ],

            // Homs
            [
                'make'     => 'Toyota',
                'model'    => 'Camry',
                'year'     => 2023,
                'city'     => 'Homs',
                'price'    => 90.00,
                'address'  => 'Syria, Homs, Al-Inshaat Street',
                'delivery' => true,
                'lat'      => 34.7200,
                'lng'      => 36.7000,
                'image'    => null,
            ],

            [
                'make'     => 'Kia',
                'model'    => 'Sportage',
                'year'     => 2022,
                'city'     => 'Homs',
                'price'    => 110.00,
                'address'  => 'Syria, Homs, Al-Inshaat Garden',
                'delivery' => false,
                'lat'      => 34.7210,
                'lng'      => 36.7020,
                'image'    => null,
            ],

            // Aleppo
            [
                'make'     => 'BMW',
                'model'    => 'X5',
                'year'     => 2024,
                'city'     => 'Aleppo',
                'price'    => 300.00,
                'address'  => 'Syria, Aleppo, Shahbaa District Near Hotel',
                'delivery' => false,
                'lat'      => 36.2200,
                'lng'      => 37.1200,
                'image'    => 'vehicles/BMW images/BMW(1).png',
            ],

            [
                'make'     => 'Audi',
                'model'    => 'A6',
                'year'     => 2023,
                'city'     => 'Aleppo',
                'price'    => 170.00,
                'address'  => 'Syria, Aleppo, Shahbaa Square',
                'delivery' => false,
                'lat'      => 36.2210,
                'lng'      => 37.1220,
                'image'    => 'vehicles/Audi/Audi A6.png',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | 3. ALL VEHICLE IMAGES
        |--------------------------------------------------------------------------
        |
        | كل صورة هنا ستتحول إلى سيارة مستقلة.
        |
        */

        $imageVehicles = [

            /*
            |--------------------------------------------------------------------------
            | AUDI
            |--------------------------------------------------------------------------
            */

            [
                'make' => 'Audi',
                'model' => 'A3',
                'year' => 2023,
                'price' => 150,
                'image' => 'vehicles/Audi/Audi A3.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'A4',
                'year' => 2023,
                'price' => 160,
                'image' => 'vehicles/Audi/Audi A4.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'A6',
                'year' => 2023,
                'price' => 170,
                'image' => 'vehicles/Audi/Audi A6.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'R8',
                'year' => 2024,
                'price' => 500,
                'image' => 'vehicles/Audi/Audi R8.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'S4',
                'year' => 2023,
                'price' => 220,
                'image' => 'vehicles/Audi/Audi S4.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'TT',
                'year' => 2022,
                'price' => 200,
                'image' => 'vehicles/Audi/Audi TT.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'A7',
                'year' => 2023,
                'price' => 280,
                'image' => 'vehicles/Audi/Audi-A7.png',
            ],

            [
                'make' => 'Audi',
                'model' => 'E-Tron',
                'year' => 2024,
                'price' => 300,
                'image' => 'vehicles/Audi/Audi-E-Tron.png',
            ],


            /*
            |--------------------------------------------------------------------------
            | BMW
            |--------------------------------------------------------------------------
            */

            [
                'make' => 'BMW',
                'model' => 'Series 1',
                'year' => 2023,
                'price' => 180,
                'image' => 'vehicles/BMW images/BMW(1).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 2',
                'year' => 2023,
                'price' => 190,
                'image' => 'vehicles/BMW images/BMW(2).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 3',
                'year' => 2023,
                'price' => 200,
                'image' => 'vehicles/BMW images/BMW(3).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 4',
                'year' => 2023,
                'price' => 220,
                'image' => 'vehicles/BMW images/BMW(4).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 5',
                'year' => 2024,
                'price' => 250,
                'image' => 'vehicles/BMW images/BMW(5).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 6',
                'year' => 2024,
                'price' => 280,
                'image' => 'vehicles/BMW images/BMW(6).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'Series 7',
                'year' => 2024,
                'price' => 350,
                'image' => 'vehicles/BMW images/BMW(7).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'X5',
                'year' => 2024,
                'price' => 300,
                'image' => 'vehicles/BMW images/BMW(8).png',
            ],

            [
                'make' => 'BMW',
                'model' => 'S3',
                'year' => 2023,
                'price' => 230,
                'image' => 'vehicles/BMW images/BMW-S3.jpg',
            ],

            [
                'make' => 'BMW',
                'model' => 'S5',
                'year' => 2024,
                'price' => 260,
                'image' => 'vehicles/BMW images/BMW0S5.png',
            ],


            /*
            |--------------------------------------------------------------------------
            | MITSUBISHI
            |--------------------------------------------------------------------------
            */

            [
                'make' => 'Mitsubishi',
                'model' => 'ASX',
                'year' => 2023,
                'price' => 90,
                'image' => 'vehicles/Mus images/Mus-ASX.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Delica',
                'year' => 2023,
                'price' => 120,
                'image' => 'vehicles/Mus images/Mus-Delica.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Electric',
                'year' => 2024,
                'price' => 140,
                'image' => 'vehicles/Mus images/Mus-Ele.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Lancer',
                'year' => 2022,
                'price' => 80,
                'image' => 'vehicles/Mus images/Mus-Lancer.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Mirage',
                'year' => 2022,
                'price' => 70,
                'image' => 'vehicles/Mus images/Mus-Mirage.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Outlander',
                'year' => 2023,
                'price' => 110,
                'image' => 'vehicles/Mus images/Mus-Oulander.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Sport',
                'year' => 2023,
                'price' => 130,
                'image' => 'vehicles/Mus images/Mus-Sport.png',
            ],

            [
                'make' => 'Mitsubishi',
                'model' => 'Triton',
                'year' => 2023,
                'price' => 125,
                'image' => 'vehicles/Mus images/Mus-Triton.png',
            ],


            /*
            |--------------------------------------------------------------------------
            | TESLA
            |--------------------------------------------------------------------------
            */

            [
                'make' => 'Tesla',
                'model' => 'Model 2',
                'year' => 2023,
                'price' => 220,
                'image' => 'vehicles/Tesla images/2.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Roadster',
                'year' => 2023,
                'price' => 400,
                'image' => 'vehicles/Tesla images/R-removebg-preview.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Roadster R',
                'year' => 2023,
                'price' => 380,
                'image' => 'vehicles/Tesla images/R.jpg',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Tesla Result',
                'year' => 2023,
                'price' => 240,
                'image' => 'vehicles/Tesla images/Tesla-result.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2023,
                'price' => 200,
                'image' => 'vehicles/Tesla images/result (1).png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model S',
                'year' => 2024,
                'price' => 300,
                'image' => 'vehicles/Tesla images/result.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model X',
                'year' => 2024,
                'price' => 350,
                'image' => 'vehicles/Tesla images/result2.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Logo',
                'year' => 2023,
                'price' => 200,
                'image' => 'vehicles/Tesla images/tesla-logo-png-image-11661594481tbbck3f4lf.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2023,
                'price' => 210,
                'image' => 'vehicles/Tesla images/tesla-model-3--removebg-preview.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2023,
                'price' => 210,
                'image' => 'vehicles/Tesla images/tesla-model-3-.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model S',
                'year' => 2024,
                'price' => 300,
                'image' => 'vehicles/Tesla images/tesla-model-s-removebg-preview.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model S',
                'year' => 2024,
                'price' => 300,
                'image' => 'vehicles/Tesla images/tesla-model-s.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Model X',
                'year' => 2024,
                'price' => 350,
                'image' => 'vehicles/Tesla images/tesla-model-x.png',
            ],

            [
                'make' => 'Tesla',
                'model' => 'Electric',
                'year' => 2024,
                'price' => 280,
                'image' => 'vehicles/Tesla images/why-choose-img.png',
            ],


            /*
            |--------------------------------------------------------------------------
            | DAIHATSU
            |--------------------------------------------------------------------------
            */

            [
                'make' => 'Daihatsu',
                'model' => 'Avanza',
                'year' => 2023,
                'price' => 75,
                'image' => 'vehicles/idaihastsu/Avanza.avif',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 1',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(1).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 2',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(2).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 3',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(3).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 4',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(4).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 5',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(5).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 6',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(6).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'IDe 7',
                'year' => 2023,
                'price' => 70,
                'image' => 'vehicles/idaihastsu/IDe(7).png',
            ],

            [
                'make' => 'Daihatsu',
                'model' => 'Tanto',
                'year' => 2023,
                'price' => 75,
                'image' => 'vehicles/idaihastsu/Tanto.webp',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | 4. MERGE ALL VEHICLES
        |--------------------------------------------------------------------------
        */

        foreach ($imageVehicles as $imageVehicle) {

            $carsPool[] = [
                'make'     => $imageVehicle['make'],
                'model'    => $imageVehicle['model'],
                'year'     => $imageVehicle['year'],
                'city'     => 'Damascus',
                'price'    => $imageVehicle['price'],
                'address'  => 'Syria, Damascus',
                'delivery' => true,
                'lat'      => 33.5000,
                'lng'      => 36.2700,
                'image'    => $imageVehicle['image'],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 5. ROLES
        |--------------------------------------------------------------------------
        */

        $guestRole = Role::query()
            ->where('name', '=', 'guest')
            ->first();

        $hostRole = Role::query()
            ->where('name', '=', 'host')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | 6. CREATE HOSTS
        |--------------------------------------------------------------------------
        */

        foreach ($hostsData as $i => $hData) {

            $user = User::create([
                'full_name'         => $hData['name'],
                'email'             => $hData['email'],
                'password'          => Hash::make('password'),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Assign Roles
            |--------------------------------------------------------------------------
            */

            $user->assignRole($guestRole);
            $user->assignRole($hostRole);

            $user->givePermissionTo(
                array_merge(
                    $guestRole->permissions->pluck('name')->toArray(),
                    $hostRole->permissions->pluck('name')->toArray()
                )
            );


            /*
            |--------------------------------------------------------------------------
            | Create Host
            |--------------------------------------------------------------------------
            */

            $host = Host::create([
                'user_id'             => $user->id,
                'total_earnings'      => 5000.00,
                'available_balance'   => 1500.00,
                'rating_avg'          => $hData['rating'],
                'total_trips'         => $hData['trips'],
                'delivery_available'  => true,
                'delivery_fee_per_km' => 5.00,
                'is_verified'         => true,
                'verified_at'         => now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 7. CREATE VEHICLES
            |--------------------------------------------------------------------------
            */

            foreach ($carsPool as $cIndex => $car) {

                /*
                |--------------------------------------------------------------------------
                | Create 2 instances
                |--------------------------------------------------------------------------
                */

                for ($instance = 1; $instance <= 2; $instance++) {

                    $vehicle = Vehicle::create([

                        'host_id' => $host->id,

                        'make' => $car['make'],

                        'model' => $car['model'] . " ($instance)",

                        'year' => $car['year'],

                        'color' => 'White Metallic',

                        'fuel_type' => 'petrol',

                        'transmission' => 'automatic',

                        'engine_capacity' => 2.4,

                        'seats' => 5,

                        'plate_number' =>
                            'Syria-' .
                            rand(100000, 999999) .
                            "-$cIndex-$instance",

                        'listing_status' => 'listed',

                        'admin_review_status' => 'approved',

                        'reviewed_at' => now(),

                        'base_price_per_day' => $car['price'],

                        'delivery_available' => $car['delivery'],

                        'delivery_fee' =>
                            $car['delivery']
                                ? 20.00
                                : null,

                        'pickup_address' => $car['address'],

                        'pickup_lat' =>
                            $car['lat'] +
                            (rand(-10, 10) / 1000),

                        'pickup_lng' =>
                            $car['lng'] +
                            (rand(-10, 10) / 1000),

                        'city' => $car['city'],

                        'total_reviews' => rand(20, 80),

                        'total_bookings' => rand(30, 100),

                        'rating_avg' =>
                            4.6 +
                            (rand(1, 3) * 0.1),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | 8. FEATURES
                    |--------------------------------------------------------------------------
                    */

                    $featureIds = Feature::inRandomOrder()
                        ->limit(3)
                        ->pluck('id')
                        ->toArray();

                    $vehicle->features()->sync($featureIds);


                    /*
                    |--------------------------------------------------------------------------
                    | 9. AVAILABILITY
                    |--------------------------------------------------------------------------
                    */

                    VehicleAvailability::create([
                        'vehicle_id' =>
                            $vehicle->id,

                        'available_from' =>
                            now()->toDateString(),

                        'available_to' =>
                            now()
                                ->addMonths(6)
                                ->toDateString(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | 10. CUSTOM PRICING
                    |--------------------------------------------------------------------------
                    */

                    if ($cIndex % 2 == 0) {

                        // Expired

                        VehicleCustomPricing::create([
                            'vehicle_id' =>
                                $vehicle->id,

                            'date_from' =>
                                now()
                                    ->subDays(20)
                                    ->toDateString(),

                            'date_to' =>
                                now()
                                    ->subDays(10)
                                    ->toDateString(),

                            'price_per_day' =>
                                $vehicle->base_price_per_day * 1.3,

                            'reason' =>
                                'Easter Holiday - Expired',
                        ]);


                        // Active now

                        VehicleCustomPricing::create([
                            'vehicle_id' =>
                                $vehicle->id,

                            'date_from' =>
                                now()
                                    ->subDays(2)
                                    ->toDateString(),

                            'date_to' =>
                                now()
                                    ->addDays(5)
                                    ->toDateString(),

                            'price_per_day' =>
                                $vehicle->base_price_per_day * 1.5,

                            'reason' =>
                                'Weekend Special - Active Now',
                        ]);


                        // Upcoming

                        VehicleCustomPricing::create([
                            'vehicle_id' =>
                                $vehicle->id,

                            'date_from' =>
                                now()
                                    ->addDays(10)
                                    ->toDateString(),

                            'date_to' =>
                                now()
                                    ->addDays(15)
                                    ->toDateString(),

                            'price_per_day' =>
                                $vehicle->base_price_per_day * 1.8,

                            'reason' =>
                                'Eid Holiday - Upcoming',
                        ]);


                        // Upcoming 2

                        VehicleCustomPricing::create([
                            'vehicle_id' =>
                                $vehicle->id,

                            'date_from' =>
                                now()
                                    ->addDays(20)
                                    ->toDateString(),

                            'date_to' =>
                                now()
                                    ->addDays(25)
                                    ->toDateString(),

                            'price_per_day' =>
                                $vehicle->base_price_per_day * 2.0,

                            'reason' =>
                                'Summer Peak - Upcoming',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | 11. VEHICLE IMAGE
                    |--------------------------------------------------------------------------
                    */

                    $imagePath = $car['image'];

                    /*
                    |--------------------------------------------------------------------------
                    | Fallback image
                    |--------------------------------------------------------------------------
                    |
                    | للسيارات التي لا يوجد لها صورة ضمن الصور التي أرسلتها.
                    |
                    */

                    if (!$imagePath) {

                        $imagePath =
                            'vehicles/Audi/Audi-E-Tron.png';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create Image
                    |--------------------------------------------------------------------------
                    */

                    Image::create([

                        'imageable_type' =>
                            Vehicle::class,

                        'imageable_id' =>
                            $vehicle->id,

                        'hash' =>
                            md5(
                                $imagePath .
                                '-' .
                                $vehicle->id .
                                '-' .
                                $instance
                            ),

                        'path' =>
                            $imagePath,

                        'sort_order' => 0,

                        'is_primary' => true,
                    ]);
                }
            }
        }
    }
}