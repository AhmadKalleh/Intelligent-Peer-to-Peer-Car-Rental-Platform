<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_custom_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('custom_price', 10, 2);
            $table->text('reason')->nullable();     // weekend / holiday / event
            $table->timestamps();

            // Indexes
            $table->index('vehicle_id');
            $table->index('date_from');
            $table->index('date_to');
            $table->index(['vehicle_id', 'date_from', 'date_to'], 'vcp_vehicle_dates_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_custom_pricings');
    }
};
