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
        Schema::create('coupon_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('restrict');
            $table->decimal('discount_applied', 10, 2);
            $table->timestampTz('used_at')->useCurrent();
        
            // ── Unique: حجز واحد = استخدام واحد ──────────────
            $table->unique('booking_id');

            // ── Unique: مستخدم واحد لكل كوبون ────────────────
            $table->unique(['coupon_id', 'user_id']);

            // Indexes
            $table->index('coupon_id');
            $table->index('user_id');
            $table->index('booking_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_uses');
    }
};
