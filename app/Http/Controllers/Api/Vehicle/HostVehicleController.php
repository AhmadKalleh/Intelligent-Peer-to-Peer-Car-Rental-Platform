<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequests\FormRequestVehicleHost;
use App\Http\Resources\Vehicle\VehicleHostListResource;
use App\Http\Resources\Vehicle\VehicleHostShowResource;
use App\Services\Vehicle\VehicleCommandService;
use Illuminate\Http\Request;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostVehicleController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected VehicleCommandService $_vehicleCommandService
    ) {}

    public function store(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $validated                  = $request->validated();
            $validated['mechanic_booklet'] = $request->file('mechanic_booklet');
            $validated['images']           = $request->file('images');

            if ($request->boolean('is_first_time')) {
                $validated['driving_license'] = $request->file('driving_license');
            }

            $result = $this->_vehicleCommandService->store($validated);

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function getHostVehicles(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {

            $result = $this->_vehicleCommandService->getHostVehicles($request->validated());

            return $this->Success([
                'vehicles'   => VehicleHostListResource::collection($result['data']),
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

    public function showForHost(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_vehicleCommandService->showForHost($request->validated()['vehicle_id']);

            return $this->Success(
                new VehicleHostShowResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
