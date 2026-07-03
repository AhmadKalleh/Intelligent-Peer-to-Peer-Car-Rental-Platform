<?php

namespace App\Repositories\User\Guest;

use App\Models\User;
use App\Repositories\User\Interfaces\UserGuestRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserGuestRepository implements UserGuestRepositoryInterface
{
    // ─── Show Guest Details ───────────────────────────────────────────────────

    public function showGuestDetails(int $userId): User
    {
        return User::query()
            ->with(['image'])
            ->findOrFail($userId);
    }

    // ─── Change Guest Password ────────────────────────────────────────────────

    public function changeGuestPassword(int $userId, array $data): array
    {
        $user = User::findOrFail($userId);

        if (!$user->hasRole('guest')) {
            return ['status' => 'not_guest'];
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Revoke all existing tokens so the guest must re-login
        $user->tokens()->delete();

        return [
            'status' => 'password_changed',
            'user'   => $user->fresh(),
        ];
    }
}
