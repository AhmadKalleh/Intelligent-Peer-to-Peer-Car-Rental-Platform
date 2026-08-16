<?php

namespace Database\Seeders;

use App\Models\Host;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Roles:
     *  - admin: full platform administration
     *  - host : vehicle owner / renter
     *  - guest: vehicle renter
     *
     * Permissions below are aligned with the CURRENT api.php routes.
     */
    public function run(): void
    {
        // ============================================================
        // 1. Roles
        // ============================================================
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $hostRole  = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'web']);
        $guestRole = Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);

        // ============================================================
        // 2. Permissions
        // ============================================================
    
        // Shared by authenticated admin / host / guest.
        $sharedPermissions = [
            'features.index',
            'vehicles.show',
            'notifications.index',
            'notifications.unread-count',
            'notifications.mark-all-read',
        ];

        // Global feature management: administrative only.
        $featureManagementPermissions = [
            'features.create',
            'features.update',
            'features.delete',
        ];

        $hostProfilePermissions = [
            'profile.show',
            'profile.image',
            'profile.change-password',
        ];

        $hostVehiclePermissions = [
            'vehicles.index-own',
            'vehicles.create',
            'vehicles.show-own',
            'vehicles.update',
            'vehicles.update-listing-status',
            'vehicles.update-location',
            'vehicle-availability.snooze',
            'vehicle-pricing.update',
            'vehicle-pricing.create',
            'vehicle-pricing.delete',
            'vehicles.images.upload',
            'vehicles.images.delete',
            'vehicles.images.set-primary',
            'vehicles.features.sync',
            'vehicle-availability.update',
        ];

        $hostSearchPermissions = [
            'search.host',
        ];

        $hostCouponPermissions = [
            'coupons.index-own',
            'coupons.create',
            'coupons.update',
            'coupons.toggle',
            'coupons.delete',
            'coupons.uses',
        ];

        $hostBookingPermissions = [
            'bookings.index-own',
            'bookings.active-own',
            'bookings.confirmed-own',
            'handover.generate',
            'reviews.rating-own',
        ];

        $guestProfilePermissions = [
            'profile.show',
            'profile.image',
            'profile.change-password',
        ];

        $guestVehiclePermissions = [
            'vehicles.browse',
            'vehicles.search',
            'vehicles.filter',
            'vehicles.location.reset',
        ];

        $favoritePermissions = [
            'favorites.index',
            'favorites.create-list',
            'favorites.show-list',
            'favorites.rename-list',
            'favorites.delete-list',
            'favorites.toggle',
            'favorites.move',
            'favorites.heart-status',
        ];

        $guestComplaintPermissions = [
            'complaints.create-own',
            'complaints.reasons',
        ];

        $conversationPermissions = [
            'conversations.open',
            'conversations.index',
            'conversations.show',
            'messages.index',
            'messages.send',
            'messages.mark-read',
            'messages.unread-count',
        ];

        $guestCouponPermissions = [
            'coupons.apply',
        ];

        $guestBookingPermissions = [
            'bookings.calculate',
            'bookings.create',
            'bookings.index-own',
            'bookings.cancel',
            'bookings.active-own',
            'bookings.confirmed-own',
            'payments.status-own',
            'handover.confirm',
        ];

        $aiChatPermissions = [
            'ai-chat.send',
            'ai-chat.messages',
            'ai-chat.unread-count',
            'ai-chat.mark-read',
        ];

        $guestReviewPermissions = [
            'reviews.create',
        ];

        $adminPermissions = [
            'admin.vehicles.index-pending',
            'admin.vehicles.show-pending',
            'admin.vehicles.approve',
            'admin.vehicles.reject',
            'admin.users.index',
            'admin.users.create',
            'admin.users.promote-to-host',
            'admin.users.toggle-status',
            'admin.users.delete-guest',
            'admin.users.delete-host',
            'admin.users.update-profile-image',
            'admin.complaints.index',
            'admin.complaints.reply',
            'admin.complaints.unanswered-count',
            'admin.search.users',
            'admin.stats.view',
        ];

        // ============================================================
        // 3. Create every permission exactly once
        // ============================================================
        $permissionGroups = [
            $sharedPermissions,
            $featureManagementPermissions,
            $hostProfilePermissions,
            $hostVehiclePermissions,
            $hostSearchPermissions,
            $hostCouponPermissions,
            $hostBookingPermissions,
            $guestProfilePermissions,
            $guestVehiclePermissions,
            $favoritePermissions,
            $guestComplaintPermissions,
            $conversationPermissions,
            $guestCouponPermissions,
            $guestBookingPermissions,
            $aiChatPermissions,
            $guestReviewPermissions,
            $adminPermissions,
        ];

        $allPermissions = collect($permissionGroups)
            ->flatten()
            ->unique()
            ->values()
            ->all();

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ============================================================
        // 4. Role assignments
        // ============================================================

        // Admin: all current API permissions.
        $adminRole->syncPermissions($allPermissions);

        // Host: only host/shared capabilities.
        $hostRole->syncPermissions(array_values(array_unique(array_merge(
            $sharedPermissions,
            $hostProfilePermissions,
            $hostVehiclePermissions,
            $hostSearchPermissions,
            $hostCouponPermissions,
            $hostBookingPermissions,
            $conversationPermissions,
        ))));

        // Guest: only guest/shared capabilities.
        $guestRole->syncPermissions(array_values(array_unique(array_merge(
            $sharedPermissions,
            $guestProfilePermissions,
            $guestVehiclePermissions,
            $favoritePermissions,
            $guestComplaintPermissions,
            $conversationPermissions,
            $guestCouponPermissions,
            $guestBookingPermissions,
            $aiChatPermissions,
            $guestReviewPermissions,
        ))));

        // ============================================================
        // 5. Seed users
        // ============================================================

        // ============================================================
        // RAGHAD - ADMIN
        // ============================================================

        $adminUser = User::firstOrCreate(
            ['email' => 'raghad@carrental.sy'],
            [
                'full_name' => 'Raghad',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $adminUser->assignRole($adminRole);

        // ============================================================
        // YARA - GUEST
        // ============================================================

        $guestUser1 = User::firstOrCreate(
            ['email' => 'yara@carrental.sy'],
            [
                'full_name' => 'Yara',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $guestUser1->assignRole($guestRole);

        // ============================================================
        // JAD - GUEST + HOST
        // ============================================================

        $guestUser2 = User::firstOrCreate(
            ['email' => 'jad@carrental.sy'],
            [
                'full_name' => 'Jad',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        // إعطاؤه دوري الـ Guest والـ Host معاً
        $guestUser2->syncRoles([$guestRole, $hostRole]);

        // إنشاء سجل الـ Host المرتبط بالمستخدم Jad
        Host::firstOrCreate(
            ['user_id' => $guestUser2->id],
            [
                'total_earnings' => 0,
                'rating_avg' => 4.95,
                'total_trips' => 50,
                'available_balance' => 0,
                'delivery_available' => true,
                'delivery_fee_per_km' => 5.00,
                'is_verified' => true,
            ]
        );

        // ============================================================
        // AHMAD - HOST
        // ============================================================

        $ahmadUser = User::firstOrCreate(
            ['email' => 'ahmad@carrental.sy'],
            [
                'full_name' => 'Ahmad',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $ahmadUser->assignRole($hostRole);

        Host::firstOrCreate(
            ['user_id' => $ahmadUser->id],
            [
                'total_earnings' => 0,
                'rating_avg' => 4.95,
                'total_trips' => 50,
                'available_balance' => 0,
                'delivery_available' => true,
                'delivery_fee_per_km' => 5.00,
                'is_verified' => true,
            ]
        );

        // ============================================================
        // HAIFA - HOST
        // ============================================================

        $haifaUser = User::firstOrCreate(
            ['email' => 'haifa@carrental.sy'],
            [
                'full_name' => 'Haifa',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $haifaUser->assignRole($hostRole);

        Host::firstOrCreate(
            ['user_id' => $haifaUser->id],
            [
                'total_earnings' => 0,
                'rating_avg' => 4.95,
                'total_trips' => 50,
                'available_balance' => 0,
                'delivery_available' => true,
                'delivery_fee_per_km' => 5.00,
                'is_verified' => true,
            ]
        );

        // ============================================================
        // SALAH - GUEST
        // ============================================================

        $salahUser = User::firstOrCreate(
            ['email' => 'salah@carrental.sy'],
            [
                'full_name' => 'Salah',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $salahUser->assignRole($guestRole);

        // ============================================================
        // GHASSAN - GUEST
        // ============================================================

        $ghassanUser = User::firstOrCreate(
            ['email' => 'ghassan@carrental.sy'],
            [
                'full_name' => 'Ghassan',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $ghassanUser->assignRole($guestRole);

        // ============================================================
        // SAEED - GUEST
        // ============================================================

        $saeedUser = User::firstOrCreate(
            ['email' => 'saeed@carrental.sy'],
            [
                'full_name' => 'Saeed',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $saeedUser->assignRole($guestRole);

        // ============================================================
        // ADAM - GUEST
        // ============================================================

        $adamUser = User::firstOrCreate(
            ['email' => 'adam@carrental.sy'],
            [
                'full_name' => 'Adam',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $adamUser->assignRole($guestRole);

        // ============================================================
        // AYLA - GUEST
        // ============================================================

        $aylaUser = User::firstOrCreate(
            ['email' => 'ayla@carrental.sy'],
            [
                'full_name' => 'Ayla',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );

        $aylaUser->assignRole($guestRole);
    }
}