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
        Schema::create('recent_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // حقول الفلترة والبحث
            $table->string('city', 100)->nullable();
            $table->string('airport_code', 10)->nullable();
            $table->decimal('lat', 10, 7)->nullable();         // إذا search_type = current_location
            $table->decimal('lng', 10, 7)->nullable();
            $table->enum('search_type', [
                'current_location',
                'anywhere',
                'city',
                'airport',
            ]);
            // تسجيل وقت البحث (يأخذ الوقت الحالي تلقائياً)
            $table->timestamp('searched_at')->useCurrent();
            // الفهارس لضمان أداء عالي وسريع عند الاستعلام
            $table->index('user_id');
            $table->index('searched_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_searches');
    }
};
