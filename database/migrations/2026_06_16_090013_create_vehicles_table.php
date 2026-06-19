<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('license_plate')->unique();
            $table->enum('transmission', ['automatic', 'manual']);
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->unsignedSmallInteger('seats');
            $table->decimal('daily_price', 10, 2);
            $table->text('description')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['available', 'unavailable', 'rented', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes للبحث السريع
            $table->index('host_id');
            $table->index('status');
            $table->index('is_active');
            $table->index('brand');
            $table->index('daily_price');
            $table->index(['brand', 'model']);
            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
