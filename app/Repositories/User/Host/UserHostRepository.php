<?php

namespace App\Repositories\User\Host;

use App\Models\Host;
use App\Models\User;
use App\Repositories\User\Interfaces\UserHostRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserHostRepository implements UserHostRepositoryInterface
{
    // ─── Show Host Details ────────────────────────────────────────────────────

    public function showHostDetails(int $hostId): Host
    {
        return Host::query()
            ->with([
                'user.image',
                'vehicles' => fn($q) => $q
                    ->with(['primaryImage'])
                    ->where('listing_status', 'listed'),
                'drivingLicense',
            ])
            ->findOrFail($hostId);
    }

    // ─── Change Host Password ─────────────────────────────────────────────────

    public function changeHostPassword(int $userId, array $data): array
    {
        $user = User::findOrFail($userId);

        if (!$user->hasRole('host')) {
            return ['status' => 'not_host'];
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Revoke all existing tokens so the host must re-login
        $user->tokens()->delete();

        return [
            'status' => 'password_changed',
            'user'   => $user->fresh(),
        ];
    }

    // ─── Get Host ID ← جديد ────────────────────────────────────────────────────
    // بيرجع hosts.id اعتمادًا على users.id (user_id عمود على جدول hosts)
    public function getHostId(int $userId): ?int
    {
        return Host::query()
            ->where('user_id', $userId)
            ->value('id');
    }
}
