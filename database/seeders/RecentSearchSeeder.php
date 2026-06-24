<?php
// database/seeders/RecentSearchSeeder.php

namespace Database\Seeders;

use App\Models\RecentSearch;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecentSearchSeeder extends Seeder
{
    public function run(): void
    {
        // جلب أي مستخدم guest موجود
        $guest = User::role('guest')->first();

        if (!$guest) {
            $this->command->warn('No guest user found! Please seed users first.');
            return;
        }

        $searches = [
            [
                'search_type'  => 'city',
                'city'         => 'Damascus',
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(5),
            ],
            [
                'search_type'  => 'city',
                'city'         => 'Aleppo',
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(10),
            ],
            [
                'search_type'  => 'city',
                'city'         => 'Homs',
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(15),
            ],
            [
                'search_type'  => 'airport',
                'city'         => null,
                'airport_code' => 'DAM',
                'lat'          => 33.4114,   // إحداثيات مطار دمشق
                'lng'          => 36.5156,
                'searched_at'  => now()->subMinutes(20),
            ],
            [
                'search_type'  => 'airport',
                'city'         => null,
                'airport_code' => 'ALP',
                'lat'          => 36.1807,   // إحداثيات مطار حلب
                'lng'          => 37.2244,
                'searched_at'  => now()->subMinutes(25),
            ],
            [
                'search_type'  => 'current_location',
                'city'         => null,
                'airport_code' => null,
                'lat'          => 33.5138,   // وسط دمشق
                'lng'          => 36.2765,
                'searched_at'  => now()->subMinutes(30),
            ],
            [
                'search_type'  => 'current_location',
                'city'         => null,
                'airport_code' => null,
                'lat'          => 36.2021,   // وسط حلب
                'lng'          => 37.1343,
                'searched_at'  => now()->subMinutes(35),
            ],
            [
                'search_type'  => 'anywhere',
                'city'         => null,
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(40),
            ],
            [
                'search_type'  => 'city',
                'city'         => 'Latakia',
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(45),
            ],
            [
                'search_type'  => 'anywhere',
                'city'         => null,
                'airport_code' => null,
                'lat'          => null,
                'lng'          => null,
                'searched_at'  => now()->subMinutes(50),
            ],
        ];

        foreach ($searches as $search) {
            RecentSearch::create([
                'user_id'      => $guest->id,
                ...$search,
            ]);
        }

        $this->command->info("✅ 10 recent searches seeded for guest: {$guest->name}");
    }
}
