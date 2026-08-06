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
        Schema::create('vehicle_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Booking::class)->constrained('bookings')->cascadeOnDelete();

            // نوع العملية: استلام السيارة عند بداية الحجز، أو تسليمها عند نهايته
            $table->enum('type', ['pickup', 'return']);

            // نخزّن هاش الكود فقط، الكود الحقيقي يُعرض مرة واحدة فقط للمالك (QR)
            $table->string('code_hash');

            $table->enum('status', ['pending', 'confirmed', 'expired'])->default('pending');

            // متى ولّد المالك الكود (يعتبر تأكيد المالك بأنه حاضر لتسليم/استلام السيارة)
            $table->timestamp('host_generated_at')->nullable();

            // متى سكن المستأجر الكود (تأكيد المستأجر)
            $table->timestamp('guest_scanned_at')->nullable();

            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index(['booking_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_handovers');
    }
};
