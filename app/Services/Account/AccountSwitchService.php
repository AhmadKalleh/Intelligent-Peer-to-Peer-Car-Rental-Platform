<?php
// app/Services/Account/AccountSwitchService.php

namespace App\Services\Account;

use App\Repositories\Account\Interfaces\AccountSwitchRepositoryInterface;

class AccountSwitchService
{
    public function __construct(
        protected AccountSwitchRepositoryInterface $_accountSwitchRepository,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SWITCH
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function switch(int $userId): array
    {
        $result = $this->_accountSwitchRepository->switch($userId);

        if ($result['status'] === 'no_host_account') {
            return [
                'data'    => [],
                'message' => 'You do not have a host account yet. Please request to become a host first.',
                'code'    => 422,
            ];
        }

        if ($result['status'] === 'no_guest_account') {
            return [
                'data'    => [],
                'message' => 'You do not have a guest account.',
                'code'    => 422,
            ];
        }

        if ($result['status'] === 'account_banned') {
            return [
                'data'    => [],
                'message' => 'Your account is currently banned.',
                'code'    => 403,
            ];
        }

        $user = $result['user'];

        return [
            'data' => [
                'active_role'       => $user->active_role,
                // 1 = الحساب الفعّال حاليًا host، 0 = الحساب الفعّال حاليًا guest
                'is_host'           => $user->active_role === 'host' ? 1 : 0,
                'has_guest_account' => $result['has_guest_account'],
                'has_host_account'  => $result['has_host_account'],
            ],
            'message' => "Switched to {$user->active_role} account successfully.",
            'code'    => 200,
        ];
    }
}
