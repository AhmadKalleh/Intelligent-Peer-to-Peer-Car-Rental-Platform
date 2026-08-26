<?php

namespace App\Repositories\User\Admin;

use App\Models\Booking;
use App\Models\Host;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\User\Interfaces\UserAdminRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserAdminRepository implements UserAdminRepositoryInterface
{
    // ─── Add User ──────────────────────────────────────────────────────────────

    public function addUser(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'full_name'         => $data['full_name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'auth_provider'     => 'local',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            $role = Role::query()->where('name', $data['role'] ?? 'guest')->first();

            if (!$role) {
                throw new \Exception('Role not found.');
            }

            $user->assignRole($role);
            $user->givePermissionTo($role->permissions->pluck('name')->toArray());

            return [
                'status' => 'created',
                'user'   => $user->fresh(),
            ];
        });
    }

    // ─── Promote Guest → Host ──────────────────────────────────────────────────

    public function promoteGuestToHost(int $userId): array
    {
        return DB::transaction(function () use ($userId) {

            $user = User::lockForUpdate()->findOrFail($userId);

            if (!$user->hasRole('guest')) {
                return ['status' => 'not_guest'];
            }

            if ($user->hasRole('host')) {
                return ['status' => 'already_host'];
            }

            // Create host profile if it doesn't exist
            $host = Host::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_earnings'    => 0,
                    'available_balance' => 0,
                    'total_trips'       => 0,
                    'delivery_available'=> false,
                    'is_verified'       => true,
                    'verified_at'       => now(),
                ]
            );

            // ← معدّل: بالسابق كان يشيل دور guest نهائيًا (removeRole).
            // هلق منحتفظ فيه ومنضيف host كمان، حتى يصير عند
            // المستخدم الحسابين مع بعض ويقدر يستخدم ميزة السويتش
            // (switch-role) للتنقل بينهم.
            $guestRole = Role::query()->where('name', 'guest')->first();
            $hostRole  = Role::query()->where('name', 'host')->first();

            if (!$hostRole) {
                throw new \Exception('Host role not found.');
            }

            if (!$user->hasRole('host')) {
                $user->assignRole($hostRole);
            }

            // دمج صلاحيات الدورين الاثنين بدل استبدالهم
            $mergedPermissions = collect($guestRole?->permissions->pluck('name') ?? [])
                ->merge($hostRole->permissions->pluck('name'))
                ->unique()
                ->toArray();

            $user->syncPermissions($mergedPermissions);

            // بما إنو صار عندو حساب host جديد، منفعّلو مباشرة كحساب حالي
            $user->update(['active_role' => 'host']);

            return [
                'status' => 'promoted',
                'user'   => $user->fresh(),
                'host'   => $host->fresh(),
            ];
        });
    }

    // ─── Delete Guest ─────────────────────────────────────────────────────────

    public function deleteGuest(int $userId): array
    {
        return DB::transaction(function () use ($userId) {

            $user = User::lockForUpdate()->findOrFail($userId);

            if (!$user->hasRole('guest')) {
                return ['status' => 'not_guest'];
            }

            // Check for active/pending bookings
            $activeBookings = Booking::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed', 'active'])
                ->count();

            if ($activeBookings > 0) {
                return [
                    'status'          => 'has_active_bookings',
                    'bookings_count'  => $activeBookings,
                    'warning'         => "This guest has {$activeBookings} active or pending booking(s). Deletion is not allowed.",
                ];
            }

            // ← جديد: منع الحذف لو عنده أي سجل حجوزات قديم (completed/cancelled)
            // bookings.user_id معمول cascade عند حذف اليوزر، لكن
            // payments.booking_id معمول restrict، فبتنكسر عملية الحذف
            // بخطأ SQL خام بدل ما ترجع رسالة واضحة. نحافظ على السجلات
            // المالية القديمة ونمنع الحذف النهائي، ونقترح الحظر بدلاً منه.
            $hasBookingHistory = Booking::query()
                ->where('user_id', $user->id)
                ->exists();

            if ($hasBookingHistory) {
                return [
                    'status'  => 'has_booking_history',
                    'warning' => 'This guest has previous booking/payment history and cannot be permanently deleted. Please ban the account instead.',
                ];
            }

            // Delete profile image from storage
            if ($user->image) {
                Storage::disk('public')->delete($user->image->path);
                $user->image()->delete();
            }

            // Revoke tokens
            $user->tokens()->delete();

            $user->delete();

            return [
                'status'    => 'deleted',
                'user_id'   => $userId,
            ];
        });
    }

    // ─── Delete Host ──────────────────────────────────────────────────────────

    public function deleteHost(int $userId): array
    {
        return DB::transaction(function () use ($userId) {

            $user = User::with(['host.vehicles'])->lockForUpdate()->findOrFail($userId);

            if (!$user->hasRole('host')) {
                return ['status' => 'not_host'];
            }

            $host = $user->host;

            if ($host) {
                $vehicleCount = $host->vehicles()->count();

                if ($vehicleCount > 0) {
                    return [
                        'status'        => 'has_vehicles',
                        'vehicle_count' => $vehicleCount,
                        'warning'       => "This host has {$vehicleCount} vehicle(s) registered on the platform. Remove all vehicles before deleting the host.",
                    ];
                }

                // ← جديد: نفس ثغرة deleteGuest بالضبط - bookings.host_id
                // معمول cascade، لكن payments.booking_id معمول restrict.
                $hasBookingHistory = Booking::query()
                    ->where('host_id', $host->id)
                    ->exists();

                if ($hasBookingHistory) {
                    return [
                        'status'  => 'has_booking_history',
                        'warning' => 'This host has previous booking/payment history and cannot be permanently deleted.',
                    ];
                }

                // Delete host images (e.g., driving license)
                foreach ($host->images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }

                $host->delete();
            }

            // Delete user profile image
            if ($user->image) {
                Storage::disk('public')->delete($user->image->path);
                $user->image()->delete();
            }

            // Revoke tokens
            $user->tokens()->delete();

            $user->delete();

            return [
                'status'  => 'deleted',
                'user_id' => $userId,
            ];
        });
    }

    // ─── Toggle Guest Status (active ↔ inactive) ──────────────────────────────

    public function toggleGuestStatus(int $userId): array
    {
        $user = User::findOrFail($userId);

        if (!$user->hasRole('guest')) {
            return ['status' => 'not_guest'];
        }

        $newStatus = $user->status === 'active' ? 'banned' : 'active';

        $user->update(['status' => $newStatus]);

        // Revoke tokens on ban
        if ($newStatus === 'banned') {
            $user->tokens()->delete();
        }

        return [
            'status'     => 'toggled',
            'new_status' => $newStatus,
            'user'       => $user->fresh(),
        ];
    }

    // ─── Get All Users (paginated) ────────────────────────────────────────────

    public function getUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['image'])
            ->withCount(['tokens'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
