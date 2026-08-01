<?php


namespace App\Services\Vehicle;

use App\Jobs\BroadcastVehicleStatusUpdatedJob;
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

                $notificationService = app(\App\Services\Notification\NotificationService::class);
                $vehicle             = $result['vehicle'];

                // ✅ إشعار كل الأدمنز في Service
                $admins = \App\Models\User::role('admin')->get();
                foreach ($admins as $admin) {
                    $notificationService->send(
                        userId         : $admin->id,
                        type           : 'vehicle_pending_review',
                        title          : $isFirstTime
                                            ? 'New Host Registration'
                                            : 'New Vehicle Submission',
                        body           : $isFirstTime
                                            ? "New host registration with vehicle {$vehicle->make} {$vehicle->model} requires your review."
                                            : "Vehicle {$vehicle->make} {$vehicle->model} submitted for review.",
                    );
                }

                // ✅ Broadcast للأدمن
                BroadcastVehicleSubmittedJob::dispatch(
                    vehicle     : $vehicle,
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

    public function updateBasicInfo(int $hostId, array $data): array
    {
        $this->_vehicleCommandRepository->updateBasicInfo($data['vehicle_id'], $hostId, $data);

        return [
            'data'    => [],
            'message' => 'Vehicle basic info updated successfully.',
            'code'    => 200,
        ];
    }

    public function updateListingStatus(int $hostId, array $data): array
    {
        $result = $this->_vehicleCommandRepository->updateListingStatus($data['vehicle_id'], $hostId, $data['listing_status']);

        if ($result['status'] === 'no_change') {
            return [
                'data'    => [],
                'message' => 'Vehicle is already in this status.',
                'code'    => 422,
            ];
        }

        return [
            'data'    => [],
            'message' => $data['listing_status'] === 'listed'
                ? 'Vehicle is now listed and visible to guests.'
                : 'Vehicle has been unlisted successfully.',
            'code'    => 200,
        ];
    }

    public function storeSnooze(int $hostId, array $data): array
    {
        $result = $this->_vehicleCommandRepository->storeSnooze($data['vehicle_id'], $hostId, $data);

        if ($result['status'] === 'error') {
            return [
                'data'    => [],
                'message' => $result['message'],
                'code'    => 422,
            ];
        }

        return [
            'data'    => $result['vehicle'],
            'message' => now()->toDateString() >= $data['snoozed_from']
                ? 'Vehicle snoozed successfully.'
                : 'Snooze scheduled successfully for ' . $data['snoozed_from'] . '.',
            'code'    => 201,
        ];
    }

    public function updatePricing(int $hostId, array $data): array
    {
        $vehicle = $this->_vehicleCommandRepository->updatePricing($data['vehicle_id'], $hostId, $data);

        return [
            'data'    => $vehicle,
            'message' => 'Vehicle pricing updated successfully.',
            'code'    => 200,
        ];
    }

    public function storeCustomPricing(int $hostId, array $data): array
    {
        $pricing = $this->_vehicleCommandRepository->storeCustomPricing($data['vehicle_id'], $hostId, $data);

        if ($pricing['status'] === 'error') {
            return [
                'data'    => [],
                'message' => $pricing['message'],
                'code'    => 422,
            ];
        }

        return [
            'data'    => [],
            'message' => 'Custom pricing period added successfully.',
            'code'    => 201,
        ];
    }

    public function updateCustomPricing(int $hostId, array $data): array
    {
        $pricing = $this->_vehicleCommandRepository->updateCustomPricing($data['vehicle_id'], $hostId, $data['custome_pricing_id'], $data);

        if ($pricing['status'] === 'error') {
            return [
                'data'    => [],
                'message' => $pricing['message'],
                'code'    => 422,
            ];
        }

        return [
            'data'    => $pricing,
            'message' => 'Custom pricing period updated successfully.',
            'code'    => 200,
        ];
    }

    public function destroyCustomPricing(int $hostId,array $data): array
    {
        $this->_vehicleCommandRepository->destroyCustomPricing($data['vehicle_id'], $hostId, $data['custome_pricing_id']);

        return [
            'data'    => [],
            'message' => 'Custom pricing period deleted successfully.',
            'code'    => 200,
        ];
    }

    public function uploadImages(int $hostId, array $data): array
    {
        $this->_vehicleCommandRepository->uploadImages($data['vehicle_id'], $hostId, $data);

        return [
            'data'    => [],
            'message' => 'Images uploaded successfully.',
            'code'    => 201,
        ];
    }

    public function destroyImage(int $hostId,array $data): array
    {
        $this->_vehicleCommandRepository->destroyImage($data['vehicle_id'], $hostId, $data['image_id']);

        return [
            'data'    => [],
            'message' => 'Image deleted successfully.',
            'code'    => 200,
        ];
    }

    public function setPrimaryImage(int $hostId,array $data): array
    {
        $this->_vehicleCommandRepository->setPrimaryImage($data['vehicle_id'], $hostId, $data['image_id']);

        return [
            'data'    => [],
            'message' => 'Primary image updated successfully.',
            'code'    => 200,
        ];
    }

    public function syncFeatures(int $hostId, array $data): array
    {
        $this->_vehicleCommandRepository->syncFeatures(
            $data['vehicle_id'],
            $hostId,
            $data['feature_ids']
        );

        return [
            'data'    => [],
            'message' => 'Vehicle features updated successfully.',
            'code'    => 200,
        ];
    }

    public function updateAvailability(int $hostId, array $data): array
    {
        $result = $this->_vehicleCommandRepository->updateAvailability($data['vehicle_id'], $hostId, $data);

        $message = match ($result['status']) {
            'reactivated' => 'Availability updated. Your vehicle has been relisted successfully.',
            'updated'     => 'Availability period updated successfully.',
            default       => 'Availability updated.',
        };

        // ── Broadcast عند إعادة التفعيل ──────────────────────
        if ($result['status'] === 'reactivated') {
            BroadcastVehicleStatusUpdatedJob::dispatch(
                vehicleId  : $data['vehicle_id'],
                hostUserId : $result['vehicle']->host->user_id,
                status     : 'listed',
                message    : 'Your vehicle has been relisted after updating availability.',
            );
        }

        return [
            'data'    => [],
            'message' => $message,
            'code'    => 200,
        ];
    }

    public function updateLocation(int $hostId, array $data): array
    {
        $this->_vehicleCommandRepository->updateLocation($data['vehicle_id'], $hostId, $data);

        return [
            'data'    => [],
            'message' => 'Vehicle location updated successfully.',
            'code'    => 200,
        ];
    }
}
