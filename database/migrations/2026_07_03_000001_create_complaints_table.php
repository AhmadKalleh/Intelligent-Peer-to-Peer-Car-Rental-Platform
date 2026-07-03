<?php

use App\Models\User;
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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            // صاحب الشكوى (Host أو Guest)
            $table->foreignIdFor(User::class, 'user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // المستخدم المشتكى عليه (Host أو Guest)
            $table->foreignIdFor(User::class, 'reported_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // سبب الشكوى (من اللائحة الثابتة)
            $table->string('reason_key');
            $table->string('reason_subject');
            $table->text('reason_text');

            // ملاحظات إضافية اختيارية من صاحب الشكوى
            $table->text('details')->nullable();

            // حالة الشكوى
            $table->enum('status', ['pending', 'answered'])->default('pending');

            // رد الأدمن
            $table->text('admin_reply')->nullable();
            $table->foreignIdFor(User::class, 'replied_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('reported_user_id');
            $table->index('status');
            $table->index(['reported_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
