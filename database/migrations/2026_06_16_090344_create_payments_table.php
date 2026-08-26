<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('restrict');
            $table->string('payment_id')->unique();     // Paymera payment_id
            $table->decimal('amount', 10, 2);
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'cancelled',
            ])->default('pending');
            $table->string('payment_url')->nullable();  // رابط صفحة الدفع
            $table->json('gateway_response')->nullable(); // رد Paymera كامل
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
