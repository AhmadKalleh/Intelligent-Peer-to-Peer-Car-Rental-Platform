<?php


namespace App\Services\Vehicle;

use App\Jobs\BroadcastVehicleSubmittedJob;
use App\Repositories\Vehicle\Interfaces\VehicleCommandRepositoryInterface;

class VehicleCommandService {
    public function __construct(
        protected VehicleCommandRepositoryInterface $_vehicleCommandRepository
    ) {}

    public function store(array $data): array
    {
        $isFirstTime = (bool) $data['is_first_time'];
        $result      = $this->_vehicleCommandRepository->store($data, $isFirstTime);

        return match ($result['status']) {

            'already_host' => [
                'data'    => [],
                'message' => 'You already have a host account.',
                'code'    => 409,
            ],

            'not_host' => [
                'data'    => [],
                'message' => 'You do not have a host account.',
                'code'    => 403,
            ],

            'pending' => tap([
                'data'    => $result['vehicle'],
                'message' => $isFirstTime
                    ? 'Your host account and vehicle have been submitted for admin review.'
                    : 'Your vehicle has been submitted for admin review.',
                'code'    => 201,
            ], function () use ($result, $isFirstTime) {
                BroadcastVehicleSubmittedJob::dispatch(
                    vehicle     : $result['vehicle'],
                    isFirstTime : $isFirstTime,
                );
            }),

            default => [
                'data'    => [],
                'message' => 'Something went wrong.',
                'code'    => 500,
            ],
        };

    }

    public function getHostVehicles(array $data): array
    {
        $paginated = $this->_vehicleCommandRepository->getHostVehicles(auth()->user()->host->id, $data['status'], 10);

        return [
            'data'    => $paginated,
            'message' => 'Host vehicles retrieved successfully.',
            'code'    => 200,
        ];
    }

    public function showForHost(int $vehicleId): array
    {
        $vehicle = $this->_vehicleCommandRepository->showForHost($vehicleId, auth()->user()->host->id);

        return [
            'data'    => $vehicle,
            'message' => 'Vehicle details retrieved successfully.',
            'code'    => 200,
        ];
    }
}
