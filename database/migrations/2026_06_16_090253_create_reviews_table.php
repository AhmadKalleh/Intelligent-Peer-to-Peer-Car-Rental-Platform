<?php

use App\Models\Booking;
use App\Models\Host;
use App\Models\User;
use App\Models\Vehicle;
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
        Schema::create('reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignIdFor(Booking::class)->constrained('bookings')->cascadeOnDelete();
        $table->foreignIdFor(Vehicle::class)->constrained('vehicles')->cascadeOnDelete();
        $table->foreignIdFor(Host::class)->constrained('hosts')->cascadeOnDelete();
        $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();

        // التقييم العام
        $table->decimal('overall_rating', 2, 1);
        $table->text('comment')->nullable();
        $table->boolean('is_visible')->default(true);

        // تقييم السيارة
        $table->decimal('cleanliness_rating', 2, 1)->nullable();
        $table->decimal('maintenance_rating', 2, 1)->nullable();
        $table->decimal('comfort_rating', 2, 1)->nullable();

        // تقييم الهوست
        $table->decimal('communication_rating', 2, 1)->nullable();
        $table->decimal('punctuality_rating', 2, 1)->nullable();

        $table->timestamps();

        // حجز واحد = تقييم واحد فقط
        $table->unique('booking_id');

        // Indexes
        $table->index(['vehicle_id', 'is_visible']);
        $table->index(['host_id', 'is_visible']);
        $table->index('user_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
