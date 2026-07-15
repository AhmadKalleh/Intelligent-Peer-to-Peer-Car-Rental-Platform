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
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('make',100);
            $table->string('model',100);
            $table->smallInteger('year');
            $table->string('color',50)->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['automatic', 'manual']);
            $table->decimal('engine_capacity',4,1)->nullable();
            $table->smallInteger('seats');
            $table->string('plate_number',30)->unique();
            $table->enum('listing_status', ['listed', 'snoozed', 'unlisted'])->default('unlisted');
            $table->enum('admin_review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_rejection_reason')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->decimal('base_price_per_day',10,2);
            $table->boolean('delivery_available')->default(false);
            $table->decimal('delivery_fee',8,2)->nullable();
            $table->text('pickup_address')->nullable();
            $table->decimal('pickup_lat',10,7)->nullable();
            $table->decimal('pickup_lng',10,7)->nullable();
            $table->string('city',100)->nullable();
            $table->text('guest_instructions')->nullable();
            $table->integer('total_reviews')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->decimal('rating_avg',3,2)->nullable();
            $table->timestamps();

            // Indexes للبحث السريع
            $table->index('host_id');
            $table->index('city');
            $table->index('make');
            $table->index('model');
            $table->index('year');
            $table->index('fuel_type');
            $table->index('listing_status');
            $table->index('admin_review_status');
            $table->index(['city','listing_status']);
            $table->index([
                'make',
                'model',
                'year'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
