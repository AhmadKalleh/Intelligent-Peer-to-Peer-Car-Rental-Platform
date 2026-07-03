<?php
// app/Http/Controllers/Api/Vehicle/VehicleController.php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequests\FormRequestVehiclePublic;
use App\Http\Resources\Vehicle\VehicleGuestShowResource;
use App\Services\Vehicle\VehicleQueryService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VehicleController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected VehicleQueryService $_vehicleQueryService
    ) {}

    private function getAuthedUser(Request $request): ?array
    {
        $user = $request->user('sanctum');
        return $user ? $user->toArray() : null;
    }

    public function all(Request $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->getAllHomeData($this->getAuthedUser($request));
            return $this->Success($result, 'Home data retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function cities(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->getCitiesData();
            return $this->Success($result, 'Top vehicles by city retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function delivery(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->getDeliveryData();
            return $this->Success($result, 'Top vehicles by delivery zone retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function airports(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->getAirportsData();
            return $this->Success($result, 'Top vehicles by airport retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function nearby(FormRequestVehiclePublic $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->getNearbyData(
                $request->validated(),
                $this->getAuthedUser($request)
            );

            if ($result['status'] === 'location_required') {
                return $this->Error(
                    [],
                    'Please share your location to fetch nearby vehicles.',
                    422
                );
            }

            return $this->Success($result['data'], 'Nearby vehicles retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function resetLocation(FormRequestVehiclePublic $request): JsonResponse
    {
        $data = [];
        try {
            $this->_vehicleQueryService->resetLocation($this->getAuthedUser($request));
            return $this->Success($data, 'Location and nearby cache reset successfully.');
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function show(FormRequestVehiclePublic $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleQueryService->show($request->validated()['vehicle_id']);
            return $this->Success(
                new VehicleGuestShowResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }




}
