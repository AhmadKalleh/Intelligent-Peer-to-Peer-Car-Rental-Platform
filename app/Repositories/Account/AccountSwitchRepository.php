<?php
// app/Repositories/Account/AccountSwitchRepository.php

namespace App\Repositories\Account;

use App\Models\User;
use App\Repositories\Account\Interfaces\AccountSwitchRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AccountSwitchRepository implements AccountSwitchRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SWITCH
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function switch(int $userId): array
    {
        return DB::transaction(function () use ($userId) {

            $user = User::lockForUpdate()->findOrFail($userId);

            $hasGuestAccount = $user->hasRole('guest');
            $hasHostAccount  = $user->hasRole('host');

            // ── الحساب الهدف = عكس الحساب الفعّال حاليًا ──────
            $targetRole = $user->active_role === 'host' ? 'guest' : 'host';

            // ── لازم يكون المستخدم يملك فعليًا الحساب الهدف ────
            if ($targetRole === 'host' && !$hasHostAccount) {
                return ['status' => 'no_host_account'];
            }

            if ($targetRole === 'guest' && !$hasGuestAccount) {
                return ['status' => 'no_guest_account'];
            }

            // ── حساب محظور ما فيه يتفعّل ───────────────────────
            if ($user->status !== 'active') {
                return ['status' => 'account_banned'];
            }

            $user->update(['active_role' => $targetRole]);

            return [
                'status'             => 'switched',
                'user'               => $user->fresh(),
                'has_guest_account'  => $hasGuestAccount,
                'has_host_account'   => $hasHostAccount,
            ];
        });
    }
}
