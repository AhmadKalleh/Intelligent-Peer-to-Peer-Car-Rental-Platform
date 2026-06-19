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
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('path');
            $table->string('url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('vehicle_id');
            $table->index(['vehicle_id', 'is_primary']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
