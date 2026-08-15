<?php

namespace Database\Seeders;

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
        $hostRole  = Role::firstOrCreate(['name' => 'host',  'guard_name' => 'web']);
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
        // 5. Seed users (idempotent)
        // ============================================================
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@carrental.sy'],
            [
                'full_name' => 'Admin User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole($adminRole);

        $guestUser = User::firstOrCreate(
            ['email' => 'guest@carrental.sy'],
            [
                'full_name' => 'Guest User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]
        );
        $guestUser->assignRole($guestRole);
    }
}
