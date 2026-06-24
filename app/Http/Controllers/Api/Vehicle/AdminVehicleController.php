<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequests\FormRequestVehicleAdmin;
use App\Http\Resources\Vehicle\VehicleAdminResource;
use App\Http\Resources\Vehicle\VehicleAdminShowResource;
use App\Services\Vehicle\VehicleAdminService;
use Illuminate\Http\Request;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AdminVehicleController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected VehicleAdminService $_vehicleAdminService
    ) {}

    public function approve(FormRequestVehicleAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleAdminService->approve($request->validated()['vehicle_id']);
            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function reject(FormRequestVehicleAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleAdminService->reject($request->validated());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function getPendingVehicles(): JsonResponse
    {
        $data = [];
        try {
            $result  = $this->_vehicleAdminService->getPendingVehicles(10);

            return $this->Success([
                'vehicles'   => VehicleAdminResource::collection($result['data']),
                'pagination' => [
                    'current_page'  => $result['data']->currentPage(),
                    'last_page'     => $result['data']->lastPage(),
                    'per_page'      => $result['data']->perPage(),
                    'total'         => $result['data']->total(),
                    'next_page_url' => $result['data']->nextPageUrl(),
                    'prev_page_url' => $result['data']->previousPageUrl(),
                ],
            ], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function showPending(FormRequestVehicleAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleAdminService->showPending($request->validated()['vehicle_id']);
            return $this->Success(
                new VehicleAdminShowResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
