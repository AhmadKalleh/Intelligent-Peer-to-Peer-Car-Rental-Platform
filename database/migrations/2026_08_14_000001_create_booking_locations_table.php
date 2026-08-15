<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Booking;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_locations', function (Blueprint $table) {
            $table->id();

            // صف واحد لكل حجز - نحدّث فيه بدل ما نضيف صفوف جديدة
            $table->foreignIdFor(Booking::class)->unique()->constrained('bookings')->cascadeOnDelete();

            // ── موقع المالك (الطرف اللي بيوصّل السيارة) ────────
            $table->decimal('host_lat', 10, 7)->nullable();
            $table->decimal('host_lng', 10, 7)->nullable();
            $table->timestamp('host_updated_at')->nullable();

            // ── موقع المستأجر (الطرف المنتظر وصول السيارة) ─────
            $table->decimal('guest_lat', 10, 7)->nullable();
            $table->decimal('guest_lng', 10, 7)->nullable();
            $table->timestamp('guest_updated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_locations');
    }
};
