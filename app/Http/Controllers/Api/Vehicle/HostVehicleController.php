<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequests\FormRequestVehicleHost;
use App\Http\Resources\Vehicle\VehicleHostListResource;
use App\Http\Resources\Vehicle\VehicleHostShowResource;
use App\Services\Vehicle\VehicleCommandService;
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

    public function updateBasicInfo(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updateBasicInfo($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function updateListingStatus(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updateListingStatus(
                $hostId,
                $request->validated()
            );

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function storeSnooze(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->storeSnooze($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function updatePricing(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updatePricing($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function storeCustomPricing(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->storeCustomPricing($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function updateCustomPricing(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updateCustomPricing($hostId,$request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function destroyCustomPricing(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->destroyCustomPricing($hostId, $request->validated());

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function uploadImages(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;

            $result = $this->_vehicleCommandService->uploadImages($hostId, $request->validated());

            return $this->Success(
                [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function destroyImage(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->destroyImage($hostId,$request->validated());

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function setPrimaryImage(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->setPrimaryImage($hostId,$request->validated());

            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function syncFeatures(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->syncFeatures($hostId, $request->validated());

            return $this->Success(
                $result['data'],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function updateAvailability(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updateAvailability($hostId, $request->validated());

            return $this->Success(
                $result['data'],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    public function updateLocation(FormRequestVehicleHost $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_vehicleCommandService->updateLocation($hostId, $request->validated());

            return $this->Success(
                $result['data'],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

}
