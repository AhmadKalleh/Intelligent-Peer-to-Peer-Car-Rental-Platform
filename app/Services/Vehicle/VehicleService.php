<?php

namespace App\Services\Vehicle;

use App\Repositories\Interfaces\VehicleRepositoryInterface;

class VehicleService
{
    public function __construct(
        protected VehicleRepositoryInterface $_vehicleRepository
    ) {}

    // =====================
    //     Get All Vehicles
    // =====================

    public function getAllVehicles(array $filters): array
    {
        $vehicles = $this->_vehicleRepository->getAllVehicles($filters);

        return [
            'data'    => $vehicles,
            'message' => 'Vehicles retrieved successfully.',
            'code'    => 200,
        ];
    }

    // =====================
    //     Get Vehicle By Id
    // =====================

    public function getVehicleById(int $id): array
    {
        $vehicle = $this->_vehicleRepository->getVehicleById($id);

        if (!$vehicle) {
            return [
                'data'    => [],
                'message' => 'Vehicle not found.',
                'code'    => 404,
            ];
        }

        return [
            'data'    => $vehicle,
            'message' => 'Vehicle retrieved successfully.',
            'code'    => 200,
        ];
    }

    // =====================
    //     Create Vehicle
    // =====================

    public function createVehicle(array $data, array $images): array
    {
        $vehicle = $this->_vehicleRepository->createVehicle($data, $images);

        return [
            'data'    => [],
            'message' => 'Vehicle created successfully.',
            'code'    => 201,
        ];
    }

    // =====================
    //     Update Vehicle
    // =====================

    public function updateVehicle(int $id, array $data, array $images): array
    {
        $existing = $this->_vehicleRepository->getVehicleById($id);

        if (!$existing) {
            return [
                'data'    => [],
                'message' => 'Vehicle not found.',
                'code'    => 404,
            ];
        }

        $vehicle = $this->_vehicleRepository->updateVehicle($id, $data, $images);

        return [
            'data'    => [],
            'message' => 'Vehicle updated successfully.',
            'code'    => 200,
        ];
    }

    // =====================
    //     Delete Vehicle
    // =====================

    public function deleteVehicle(int $id): array
    {
        $existing = $this->_vehicleRepository->getVehicleById($id);

        if (!$existing) {
            return [
                'data'    => [],
                'message' => 'Vehicle not found.',
                'code'    => 404,
            ];
        }

        $this->_vehicleRepository->deleteVehicle($id);

        return [
            'data'    => [],
            'message' => 'Vehicle deleted successfully.',
            'code'    => 200,
        ];
    }
}
