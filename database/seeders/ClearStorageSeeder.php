<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ClearStorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('vehicles');
        Storage::disk('public')->deleteDirectory('resources');

        Storage::disk('public')->makeDirectory('vehicles');
        Storage::disk('public')->makeDirectory('resources');
    }
}
