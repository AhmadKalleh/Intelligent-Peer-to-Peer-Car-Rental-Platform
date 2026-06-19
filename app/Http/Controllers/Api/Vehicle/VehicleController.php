<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequests\FormRequestVehicle;
use App\Services\Vehicle\VehicleService;
use Illuminate\Http\JsonResponse;
use App\Traits\ResponseHelper\ResponseHelper;
use Throwable;

class VehicleController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected VehicleService $_vehicleService
    ) {}

    // =====================
    //     عرض كل السيارات
    //     host   → vehicles.index   (سياراته هو)
    //     guest  → vehicles.browse  (تصفح السيارات المتاحة)
    // =====================

    public function index(FormRequestVehicle $request): JsonResponse
    {
        $data = [];

        try {
            // Host يشوف سياراته | Guest يتصفح السيارات المتاحة
            if (! ($request->user()->can('vehicles.index') || $request->user()->can('vehicles.browse'))) {
                return $this->Error([], 'You do not have permission to view vehicles.', 403);
            }

            $data = $this->_vehicleService->getAllVehicles($request->validated());
            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    // =====================
    //     عرض سيارة واحدة
    //     host  → vehicles.show
    //     guest → vehicles.show-public
    // =====================

    public function show(int $id): JsonResponse
    {
        $data = [];

        try {
            if (! (auth()->user()->can('vehicles.show') || auth()->user()->can('vehicles.show-public'))) {
                return $this->Error([], 'You do not have permission to view this vehicle.', 403);
            }

            $data = $this->_vehicleService->getVehicleById($id);
            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    // =====================
    //     إضافة سيارة
    //     host فقط → vehicles.create
    // =====================

    public function store(FormRequestVehicle $request): JsonResponse
    {
        $data = [];

        try {
            if (! $request->user()->can('vehicles.create')) {
                return $this->Error([], 'You do not have permission to create a vehicle.', 403);
            }

            $images = $request->hasFile('images') ? $request->file('images') : [];

            $data = $this->_vehicleService->createVehicle(
                $request->validated(),
                $images
            );

            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    // =====================
    //     تعديل بيانات سيارة
    //     host فقط → vehicles.update
    // =====================

    public function update(FormRequestVehicle $request, int $id): JsonResponse
    {
        $data = [];

        try {
            if (! $request->user()->can('vehicles.update')) {
                return $this->Error([], 'You do not have permission to update a vehicle.', 403);
            }

            $images = $request->hasFile('images') ? $request->file('images') : [];

            $data = $this->_vehicleService->updateVehicle(
                $id,
                $request->validated(),
                $images
            );

            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    // =====================
    //     حذف سيارة
    //     host فقط → vehicles.delete
    // =====================

    public function destroy(int $id): JsonResponse
    {
        $data = [];

        try {
            if (! auth()->user()->can('vehicles.delete')) {
                return $this->Error([], 'You do not have permission to delete a vehicle.', 403);
            }

            $data = $this->_vehicleService->deleteVehicle($id);
            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }
}
