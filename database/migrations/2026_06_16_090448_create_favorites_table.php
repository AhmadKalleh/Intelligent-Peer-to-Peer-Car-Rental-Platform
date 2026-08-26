<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * favorites:
     *   - user_id       → المستخدم (Guest) الذي أضاف المفضلة
     *   - vehicle_id    → السيارة المضافة إلى المفضلة
     *   - created_at    → تاريخ الإضافة (يُستخدم للترتيب)
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();
            $table->timestamps();

            // منع تكرار نفس السيارة في مفضلة نفس المستخدم
            $table->unique(['user_id', 'vehicle_id']);

            // فهارس لتسريع الاستعلامات
            $table->index('user_id');
            $table->index('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
