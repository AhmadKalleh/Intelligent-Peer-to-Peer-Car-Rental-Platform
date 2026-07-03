<?php


namespace App\Repositories\Vehicle\Interfaces;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;

interface VehicleAdminRepositoryInterface
{
    public function approve(int $vehicleId, int $adminId): array;
    public function reject(int $vehicleId, int $adminId, string $reason): array;

    public function getPendingVehicles(int $perPage): LengthAwarePaginator;
    public function showPending(int $vehicleId): Vehicle;
}
