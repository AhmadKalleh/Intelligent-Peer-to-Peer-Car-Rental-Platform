<?php
// app/Http/Controllers/Api/Account/AccountSwitchController.php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Services\Account\AccountSwitchService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class AccountSwitchController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected AccountSwitchService $_accountSwitchService
    ) {}

    // ─── POST /api/switch-role ──────────────────────────────
    // يبدّل حساب المستخدم الحالي المسجّل دخوله بين guest و host
    public function switch(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_accountSwitchService->switch(auth()->id());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
