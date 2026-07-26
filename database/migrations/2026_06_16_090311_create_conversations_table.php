<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('conversations');

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // الغيست (user_id صاحب role guest)
            $table->foreignId('guest_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // الهوست (user_id صاحب role host)
            $table->foreignId('host_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // آخر رسالة (للعرض السريع في قائمة المحادثات)
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();

            // عدد الرسائل غير المقروءة لكل طرف
            $table->unsignedInteger('guest_unread_count')->default(0);
            $table->unsignedInteger('host_unread_count')->default(0);

            $table->timestamps();

            // محادثة واحدة بين الغيست والهوست
            $table->unique(['guest_user_id', 'host_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
