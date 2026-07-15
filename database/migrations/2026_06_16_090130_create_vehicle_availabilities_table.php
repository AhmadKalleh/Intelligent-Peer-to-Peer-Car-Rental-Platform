<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->date('available_from');
            $table->date('available_to');
            $table->boolean('is_blocked')->default(false); // لتحديد فترات الحجب
            $table->enum('type', [
                'available',      // إتاحة عادية من الهوست
                'snoozed',        // حجب من الهوست
                'booking_block',  // حجب تلقائي عند الحجز
            ])->default('available');
            $table->text('block_reason')->nullable();
            $table->enum('blocked_by',['system','host'])->nullable();
            $table->timestamps();

            // Indexes
            $table->index('vehicle_id');
            $table->index('available_from');
            $table->index('available_to');
            $table->index(['vehicle_id', 'available_from', 'available_to'], 'va_vehicle_dates_index');
            $table->index('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_availabilities');
    }
};
