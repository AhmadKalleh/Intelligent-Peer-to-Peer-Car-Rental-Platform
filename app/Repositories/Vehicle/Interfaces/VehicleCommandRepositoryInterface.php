<?php


namespace App\Repositories\Vehicle\Interfaces;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VehicleCommandRepositoryInterface
{
    public function store(array $data, bool $isFirstTime): array;
    public function getHostVehicles(int $hostId, string $status, int $perPage): LengthAwarePaginator;
    public function showForHost(int $vehicleId, int $hostId): Vehicle;
}
