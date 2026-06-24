<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guest;
use App\Models\Host;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Roles:
     *   - admin   → الإداري       : يشرف على المنصة بالكامل
     *   - host    → المضيف        : يملك السيارات ويؤجرها
     *   - guest   → المستأجر      : يبحث ويحجز السيارات
     */
    public function run(): void
    {
        // ────────────────────────────────────────────────
        // 1. إنشاء الأدوار
        // ────────────────────────────────────────────────
        $admin_role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $host_role  = Role::create(['name' => 'host',  'guard_name' => 'web']);
        $guest_role = Role::create(['name' => 'guest', 'guard_name' => 'web']);


        // ────────────────────────────────────────────────
        // 2. تعريف الصلاحيات لكل وحدة
        // ────────────────────────────────────────────────

        // ── 2.1 إدارة الحساب الشخصي (مشتركة بين الأدوار) ──
        $profile_permissions = [
            'profile.show',             // عرض الملف الشخصي
            'profile.update',           // تعديل الاسم والصورة
        ];

        // ── 2.2 إدارة السيارات (Host) ──
        $vehicle_permissions = [
            'vehicles.create',                  // إضافة سيارة جديدة
            'vehicles.update',                  // تعديل بيانات السيارة
            'vehicles.delete',                  // حذف السيارة نهائياً
            'vehicles.show',                    // عرض تفاصيل سيارة
            'vehicles.index',                   // عرض قائمة سياراته
            'vehicles.update-listing-status',   // تغيير حالة العرض (listed/snoozed/unlisted)
            'vehicles.update-location',         // تحديث موقع الاستلام
            'vehicles.update-instructions',     // تعديل تعليمات المستأجر
            'vehicles.update-delivery',         // إعداد خيار التوصيل والرسوم
        ];

        // ── 2.3 إدارة ميزات السيارة (Host) ──
        $vehicle_features_permissions = [
            'vehicle-features.create',  // إضافة ميزة
            'vehicle-features.update',  // تعديل ميزة
            'vehicle-features.delete',  // حذف ميزة
            'vehicle-features.index',   // عرض ميزات السيارة
        ];

        // ── 2.4 تسعير مخصص (Host) ──
        $custom_pricing_permissions = [
            'vehicle-pricing.create',   // إضافة سعر ليوم محدد
            'vehicle-pricing.update',   // تعديل السعر
            'vehicle-pricing.delete',   // حذف السعر المخصص
            'vehicle-pricing.index',    // عرض الأسعار المخصصة
        ];

        // ── 2.5 إدارة التوفر (Host) ──
        $availability_permissions = [
            'vehicle-availability.create',  // إضافة فترة توفر أو حجب
            'vehicle-availability.update',  // تعديل الفترة
            'vehicle-availability.delete',  // حذف الفترة
            'vehicle-availability.index',   // عرض جدول التوفر
        ];

        // ── 2.6 الحجوزات ──
        $booking_permissions_host = [
            'bookings.index-own',       // Host: عرض حجوزاته
            'bookings.show',            // Host: تفاصيل حجز معين
            'bookings.confirm',         // Host: قبول طلب الحجز
            'bookings.reject',          // Host: رفض طلب الحجز
            'bookings.start',           // Host: تفعيل الحجز (بدء الرحلة)
            'bookings.complete',        // Host: إنهاء الحجز
        ];

        $booking_permissions_guest = [
            'bookings.create',          // Guest: إنشاء حجز جديد
            'bookings.cancel',          // Guest: إلغاء الحجز
            'bookings.index-own',       // Guest: عرض حجوزاته
            'bookings.show',            // Guest: تفاصيل حجز معين
        ];

        // ── 2.7 التقييمات ──
        $review_permissions = [
            'reviews.create',           // إنشاء تقييم بعد اكتمال الحجز
            'reviews.show',             // عرض تقييم معين
            'reviews.index-vehicle',    // عرض تقييمات سيارة
        ];

        // ── 2.8 المحادثات والرسائل ──
        $conversation_permissions_shared = [
            'conversations.index',      // عرض قائمة المحادثات
            'conversations.show',       // فتح محادثة
            'messages.send',            // إرسال رسالة (نص أو صورة)
            'messages.index',           // عرض رسائل محادثة
            'messages.mark-read',       // تعليم الرسائل كمقروءة
        ];

        $conversation_support = [
            'conversations.open-support', // فتح محادثة دعم مع الأدمن
        ];

        // ── 2.9 الإشعارات ──
        $notification_permissions = [
            'notifications.index',      // عرض إشعاراته
            'notifications.mark-read',  // تعليم إشعار كمقروء
            'notifications.mark-all-read', // تعليم الكل كمقروء
        ];

        // ── 2.10 الكوبونات (Host) ──
        $coupon_permissions = [
            'coupons.create',           // إنشاء كوبون خصم
            'coupons.update',           // تعديل الكوبون
            'coupons.delete',           // حذف الكوبون
            'coupons.index',            // عرض كوبوناته
            'coupons.show',             // تفاصيل كوبون
        ];

        // ── 2.11 استخدام الكوبون (Guest) ──
        $coupon_use_permissions = [
            'coupons.apply',            // تطبيق كوبون عند الحجز
        ];

        // ── 2.12 المدفوعات ──
        $payment_permissions_guest = [
            'payments.create',          // دفع حجز
            'payments.index-own',       // عرض سجل مدفوعاته
            'payments.show',            // تفاصيل عملية دفع
        ];

        $payout_permissions_host = [
            'host-payouts.index',       // عرض سجل الأرباح
            'host-payouts.show',        // تفاصيل صرفية
            'host-payouts.request',     // طلب سحب الرصيد
        ];

        // ── 2.13 الاكتشاف والبحث (Guest) ──
        $discovery_permissions = [
            'vehicles.browse',          // تصفح السيارات المتاحة
            'vehicles.search',          // البحث بالفلاتر
            'vehicles.show-public',     // عرض صفحة سيارة للعموم
            'recent-searches.index',    // عرض بحثه الأخير
            'recent-searches.clear',    // مسح سجل البحث
        ];

        // ── 2.14 المفضلة (Guest) ──
        $favorites_permissions = [
            'favorites.create',         // إضافة سيارة للمفضلة
            'favorites.delete',         // إزالة من المفضلة
            'favorites.index',          // عرض المفضلة
        ];

        // ── 2.15 الصور ──
        $image_permissions_shared = [
            'images.upload',            // رفع صورة (للملف الشخصي أو السيارة)
            'images.delete-own',        // حذف صورة خاصة به
        ];

        // ── 2.16 التتبع الجغرافي (Host أثناء التوصيل) ──
        $location_permissions = [
            'location.broadcast',       // Host: بث الموقع اللحظي
            'location.view',            // Guest: مشاهدة موقع التوصيل
        ];

        // ── 2.17 صلاحيات الأدمن الحصرية ──
        $admin_permissions = [
            // إدارة المستخدمين
            'admin.users.index',            // عرض جميع المستخدمين
            'admin.users.show',             // تفاصيل مستخدم
            'admin.users.suspend',          // تعليق حساب
            'admin.users.activate',         // تفعيل حساب

            // اعتماد المستأجرين
            'admin.guests.approve-license', // قبول رخصة قيادة
            'admin.guests.reject-license',  // رفض رخصة مع سبب

            // اعتماد السيارات
            'admin.vehicles.index-pending', // عرض السيارات المعلقة
            'admin.vehicles.approve',       // اعتماد سيارة
            'admin.vehicles.reject',        // رفض سيارة مع سبب
            'admin.vehicles.force-unlist',  // إخفاء سيارة قسراً

            // إدارة الحجوزات
            'admin.bookings.index',         // عرض جميع الحجوزات
            'admin.bookings.show',          // تفاصيل حجز

            // الدعم الفني
            'admin.conversations.index',    // عرض محادثات الدعم
            'admin.conversations.reply',    // الرد على المستخدم

            // التقارير والإحصاء
            'admin.stats.view',             // لوحة إحصائيات المنصة

            // إدارة المدفوعات
            'admin.payments.index',         // عرض جميع المدفوعات
            'admin.payouts.process',        // معالجة صرف أرباح المضيف
        ];


        // ────────────────────────────────────────────────
        // 3. تسجيل جميع الصلاحيات في قاعدة البيانات
        // ────────────────────────────────────────────────
        $all_permissions = array_merge(
            $profile_permissions,
            $vehicle_permissions,
            $vehicle_features_permissions,
            $custom_pricing_permissions,
            $availability_permissions,
            $booking_permissions_host,
            $booking_permissions_guest,
            $review_permissions,
            $conversation_permissions_shared,
            $conversation_support,
            $notification_permissions,
            $coupon_permissions,
            $coupon_use_permissions,
            $payment_permissions_guest,
            $payout_permissions_host,
            $discovery_permissions,
            $favorites_permissions,
            $image_permissions_shared,
            $location_permissions,
            $admin_permissions,
        );

        // إزالة التكرار ثم الإنشاء
        foreach (array_unique($all_permissions) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }


        // ────────────────────────────────────────────────
        // 4. تعيين الصلاحيات للأدوار
        // ────────────────────────────────────────────────

        // ── Admin: كل الصلاحيات ──────────────────────────
        $admin_role->syncPermissions(array_unique($all_permissions));


        // ── Host ─────────────────────────────────────────
        $host_role->syncPermissions(array_unique(array_merge(
            $profile_permissions,
            $vehicle_permissions,
            $vehicle_features_permissions,
            $custom_pricing_permissions,
            $availability_permissions,
            $booking_permissions_host,
            $review_permissions,
            $conversation_permissions_shared,
            $conversation_support,
            $notification_permissions,
            $coupon_permissions,
            $payout_permissions_host,
            $image_permissions_shared,
            $location_permissions,    // location.broadcast
        )));


        // ── Guest ─────────────────────────────────────────
        $guest_role->syncPermissions(array_unique(array_merge(
            $profile_permissions,
            $booking_permissions_guest,
            $review_permissions,
            $conversation_permissions_shared,
            $conversation_support,
            $notification_permissions,
            $coupon_use_permissions,
            $payment_permissions_guest,
            $discovery_permissions,
            $favorites_permissions,
            $image_permissions_shared,
            [
                'location.view',        // مشاهدة موقع التوصيل فقط
            ],
        )));



        // ────────────────────────────────────────────────
        // 5. إنشاء المستخدمين الأوليين (Seed Data)
        // ────────────────────────────────────────────────

        // ══ 5.1 Admin ════════════════════════════════════
        $admin_user = User::query()->create([
            'full_name'         => 'Admin User',
            'email'             => 'admin@carrental.sy',
            'password'     => Hash::make('password'),
            'status'            => 'active',
            'auth_provider'      => 'local',
            'email_verified_at' => now(),
        ]);

        $admin_user->assignRole($admin_role);
        $admin_user->givePermissionTo(
            $admin_role->permissions()->pluck('name')->toArray()
        );


        // ══ 5.2 Host ════════════════════════════════════

        // ══ 5.3 Guest ════════════════════════════════════
        $guest_user = User::query()->create([
            'full_name'         => 'Guest User',
            'email'             => 'guest@carrental.sy',
            'password'     => Hash::make('password'),
            'status'            => 'active',
            'auth_provider'      => 'local',
            'email_verified_at' => now(),
        ]);

        $guest_user->assignRole($guest_role);
        $guest_user->givePermissionTo(
            $guest_role->permissions()->pluck('name')->toArray()
        );

    }
}
