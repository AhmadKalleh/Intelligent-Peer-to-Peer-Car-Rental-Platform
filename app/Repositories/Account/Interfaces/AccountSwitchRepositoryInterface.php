<?php
// app/Repositories/Account/Interfaces/AccountSwitchRepositoryInterface.php

namespace App\Repositories\Account\Interfaces;

interface AccountSwitchRepositoryInterface
{
    /**
     * يبدّل الحساب الفعّال للمستخدم بين guest و host.
     */
    public function switch(int $userId): array;
}
