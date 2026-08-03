<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClearStorageSeeder::class,
            RolesPermissionsSeeder::class,
            FeatureSeeder::class,
            VehicleSeeder::class,
            //BookingAndReviewSeeder::class,
            CouponSeeder::class,
            //CouponUseSeeder::class,
        ]);
    }
}
