<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Host;
use App\Models\User;
use App\Models\Vehicle;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vehicle::class)->constrained('vehicles')->cascadeOnDelete();
            $table->foreignIdFor(Host::class)->constrained('hosts')->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();
            // التواريخ
            $table->date('start_date');
            $table->date('end_date');
            $table->smallInteger('total_days');

            // الأسعار
            $table->decimal('base_price_per_day', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // التوصيل
            $table->enum('delivery_type', ['pickup', 'delivery']);
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();

            // الحالة
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])
                    ->default('pending');

            $table->text('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['guest', 'host', 'admin'])->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('host_id');
            $table->index('vehicle_id');
            $table->index('status');

            $table->index(['user_id', 'status']);
            $table->index(['host_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
