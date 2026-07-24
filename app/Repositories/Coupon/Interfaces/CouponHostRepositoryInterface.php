<?php
// app/Repositories/Coupon/Interfaces/CouponHostRepositoryInterface.php

namespace App\Repositories\Coupon\Interfaces;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CouponHostRepositoryInterface
{
    public function index(int $hostId): Collection;
    public function store(int $hostId, array $data): Coupon;
    public function update(int $hostId, int $couponId, array $data): Coupon;
    public function toggleStatus(int $hostId, int $couponId): Coupon;
    public function destroy(int $hostId, int $couponId): array;
    public function showUses(int $hostId, int $couponId): Coupon;
}
