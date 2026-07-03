<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // نحذف الجدول القديم الفارغ ونستبدله بالجديد
        Schema::dropIfExists('favorites');

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();
            $table->foreignId('favorite_list_id')
                  ->constrained('favorite_lists')
                  ->cascadeOnDelete();
            $table->timestamps();

            // لا يمكن إضافة نفس السيارة في نفس الليستا مرتين
            $table->unique(['user_id', 'vehicle_id', 'favorite_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
