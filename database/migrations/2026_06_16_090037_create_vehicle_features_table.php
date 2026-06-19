<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('feature_name');       // GPS, Bluetooth, AirConditioning...
            $table->string('feature_value')->nullable(); // yes/no or extra info
            $table->timestamps();

            // Indexes
            $table->index('vehicle_id');
            $table->index('feature_name');
            $table->unique(['vehicle_id', 'feature_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_features');
    }
};
