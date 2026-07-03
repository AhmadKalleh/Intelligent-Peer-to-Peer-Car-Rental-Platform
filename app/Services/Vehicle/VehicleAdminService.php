<?php


namespace App\Services\Vehicle;

use App\Jobs\BroadcastVehicleStatusUpdatedJob;
use App\Repositories\Vehicle\Interfaces\VehicleAdminRepositoryInterface;

class VehicleAdminService
{
    public function __construct(
        protected VehicleAdminRepositoryInterface $_vehicleAdminRepository
    ) {}

    public function approve(int $vehicleId): array
    {
        $result = $this->_vehicleAdminRepository->approve($vehicleId, auth()->id());

        if ($result['status'] === 'not_pending') {
            return [
                'data'    => [],
                'message' => 'This vehicle is not in pending status.',
                'code'    => 422,
            ];
        }

        // ── Pusher: إشعار الهوست لحظياً ──────────────────────
        BroadcastVehicleStatusUpdatedJob::dispatch(
            vehicleId  : $vehicleId,
            hostUserId : $result['host_user_id'],
            status     : 'approved',
            message    : $result['is_first_time']
                ? 'Congratulations! Your host account is now active and your vehicle is listed.'
                : 'Your vehicle has been approved and is now listed.',
        );

        return [
            'data'    => $result['vehicle'],
            'message' => 'Vehicle approved successfully.',
            'code'    => 200,
        ];
    }

    public function reject(array $data): array
    {
        $result = $this->_vehicleAdminRepository->reject($data['vehicle_id'], auth()->id(), $data['reason']);

        if ($result['status'] === 'not_pending') {
            return [
                'data'    => [],
                'message' => 'This vehicle is not in pending status.',
                'code'    => 422,
            ];
        }

        // ── Pusher: إشعار الهوست لحظياً ──────────────────────
        BroadcastVehicleStatusUpdatedJob::dispatch(
            vehicleId  : $data['vehicle_id'],
            hostUserId : $result['host_user_id'],
            status     : 'rejected',
            message    : $result['is_first_time']
                ? 'Your host registration has been rejected. Please contact support.'
                : "Your vehicle {$result['vehicle_make']} {$result['vehicle_model']} was rejected. Reason: {$data['reason']}",
        );

        return [
            'data'    => [],
            'message' => $result['is_first_time']
                ? 'Host registration rejected and all data removed.'
                : 'Vehicle rejected successfully.',
            'code'    => 200,
        ];
    }

    public function getPendingVehicles(int $perPage = 10): array
    {
        $paginated = $this->_vehicleAdminRepository->getPendingVehicles($perPage);

        return [
            'data'    => $paginated,
            'message' => 'Pending vehicles retrieved successfully.',
            'code'    => 200,
        ];
    }

    public function showPending(int $vehicleId): array
    {
        $vehicle = $this->_vehicleAdminRepository->showPending($vehicleId);

        return [
            'data'    => $vehicle,
            'message' => 'Pending vehicle details retrieved successfully.',
            'code'    => 200,
        ];
    }
}
