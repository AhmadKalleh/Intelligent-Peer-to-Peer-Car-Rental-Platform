<?php

namespace App\Repositories\Interfaces;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;

interface VehicleRepositoryInterface
{
    public function getAllVehicles(array $filters): LengthAwarePaginator;
    public function getVehicleById(int $id): ?Vehicle;
    public function createVehicle(array $data, array $images): Vehicle;
    public function updateVehicle(int $id, array $data, array $images): Vehicle;
    public function deleteVehicle(int $id): bool;
}
