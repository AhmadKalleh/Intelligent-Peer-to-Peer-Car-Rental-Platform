<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // الحساب "الفعّال" حاليًا: guest أو host (لتحديد أي واجهة يشوف المستخدم)
            $table->enum('active_role', ['guest', 'host'])->default('guest')->after('status');
        });

        // ─── Backfill ──────────────────────────────────────────
        // promoteGuestToHost القديمة كانت تشيل دور guest نهائيًا
        // عند الترقية لـ host. بعد التصليح، أي مستخدم عندو host
        // بس وما عندو guest، منرجّعلو دور guest حتى يقدر يستخدم
        // ميزة السويتش (وهو أصلًا شغال حاليًا كـ host فمنحط
        // active_role = host له مباشرة).
        $guestRoleId = DB::table('roles')->where('name', 'guest')->value('id');
        $hostRoleId  = DB::table('roles')->where('name', 'host')->value('id');

        if ($guestRoleId && $hostRoleId) {

            $hostUserIds = DB::table('model_has_roles')
                ->where('role_id', $hostRoleId)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($hostUserIds as $userId) {

                $hasGuestRole = DB::table('model_has_roles')
                    ->where('role_id', $guestRoleId)
                    ->where('model_type', User::class)
                    ->where('model_id', $userId)
                    ->exists();

                if (!$hasGuestRole) {
                    DB::table('model_has_roles')->insert([
                        'role_id'    => $guestRoleId,
                        'model_type' => User::class,
                        'model_id'   => $userId,
                    ]);
                }

                DB::table('users')->where('id', $userId)->update(['active_role' => 'host']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_role');
        });
    }
};
