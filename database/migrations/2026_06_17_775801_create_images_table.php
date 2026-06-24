<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('imageable');
            $table->string('path');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->enum('type',['vehicle_image','mechanic_booklet','driving_license','profile_image']);
            $table->timestamps();

            // Indexes
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
