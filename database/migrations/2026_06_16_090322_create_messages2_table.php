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
        Schema::create('messages2', function (Blueprint $table) {
            $table->id();

           $table->foreignId('conversation_id')
                ->constrained('conversations2')
                ->cascadeOnDelete();

            // مين بعت الرسالة: الضيف أو المساعد الذكي
            $table->enum('sender', ['guest', 'ai']);

            $table->text('content');

            // تُستخدم فقط لرسائل الـ AI: هل قرأها الضيف أم لا
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages2');
    }
};
