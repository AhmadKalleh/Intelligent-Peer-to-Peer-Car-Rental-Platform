<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Traits\Upload\UplodeImageHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleRepository implements VehicleRepositoryInterface
{
    use UplodeImageHelper;

    private const CACHE_TTL    = 600;
    private const CACHE_PREFIX = 'vehicles';

    // =====================
    //     Get All Vehicles
    // =====================

    public function getAllVehicles(array $filters): LengthAwarePaginator
    {
        $version  = $this->getListVersion();
        $cacheKey = self::CACHE_PREFIX . ':list:v' . $version . ':' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {

            $query = Vehicle::with(['images', 'features'])
                ->where('is_active', true);

            if (!empty($filters['brand'])) {
                $query->where('brand', 'like', '%' . $filters['brand'] . '%');
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['min_price'])) {
                $query->where('daily_price', '>=', $filters['min_price']);
            }

            if (!empty($filters['max_price'])) {
                $query->where('daily_price', '<=', $filters['max_price']);
            }

            if (!empty($filters['transmission'])) {
                $query->where('transmission', $filters['transmission']);
            }

            if (!empty($filters['fuel_type'])) {
                $query->where('fuel_type', $filters['fuel_type']);
            }

            $perPage = $filters['per_page'] ?? 10;

            return $query->latest()->paginate($perPage);
        });
    }

    // =====================
    //     Get Vehicle By Id
    // =====================

    public function getVehicleById(int $id): ?Vehicle
    {
        $cacheKey = self::CACHE_PREFIX . ':show:' . $id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Vehicle::with([
                'images',
                'features',
                'availabilities',
                'customPricings',
                'host',
            ])->find($id);
        });
    }

    // =====================
    //     Create Vehicle
    // =====================

    public function createVehicle(array $data, array $images): Vehicle
    {
        return DB::transaction(function () use ($data, $images) {

            $vehicle = Vehicle::create([
                'host_id'       => $data['host_id'],
                'brand'         => $data['brand'],
                'model'         => $data['model'],
                'year'          => $data['year'],
                'color'         => $data['color'],
                'license_plate' => $data['license_plate'],
                'transmission'  => $data['transmission'],
                'fuel_type'     => $data['fuel_type'],
                'seats'         => $data['seats'],
                'daily_price'   => $data['daily_price'],
                'description'   => $data['description'] ?? null,
                'location'      => $data['location'],
                'latitude'      => $data['latitude'] ?? null,
                'longitude'     => $data['longitude'] ?? null,
                'status'        => 'available',
                'is_active'     => true,
            ]);

            if (!empty($images)) {
                $this->storeImages($vehicle, $images);
            }

            if (!empty($data['features'])) {
                $this->storeFeatures($vehicle, $data['features']);
            }

            if (!empty($data['availabilities'])) {
                $this->storeAvailabilities($vehicle, $data['availabilities']);
            }

            if (!empty($data['custom_pricings'])) {
                $this->storeCustomPricings($vehicle, $data['custom_pricings']);
            }

            $this->clearListCache();

            return $vehicle->load(['images', 'features', 'availabilities', 'customPricings']);
        });
    }

    // =====================
    //     Update Vehicle
    // =====================

    public function updateVehicle(int $id, array $data, array $images): Vehicle
    {
        return DB::transaction(function () use ($id, $data, $images) {

            $vehicle = Vehicle::findOrFail($id);

            $vehicle->update([
                'brand'         => $data['brand']         ?? $vehicle->brand,
                'model'         => $data['model']         ?? $vehicle->model,
                'year'          => $data['year']          ?? $vehicle->year,
                'color'         => $data['color']         ?? $vehicle->color,
                'license_plate' => $data['license_plate'] ?? $vehicle->license_plate,
                'transmission'  => $data['transmission']  ?? $vehicle->transmission,
                'fuel_type'     => $data['fuel_type']     ?? $vehicle->fuel_type,
                'seats'         => $data['seats']         ?? $vehicle->seats,
                'daily_price'   => $data['daily_price']   ?? $vehicle->daily_price,
                'description'   => $data['description']   ?? $vehicle->description,
                'location'      => $data['location']      ?? $vehicle->location,
                'latitude'      => $data['latitude']      ?? $vehicle->latitude,
                'longitude'     => $data['longitude']     ?? $vehicle->longitude,
                'status'        => $data['status']        ?? $vehicle->status,
            ]);

            if (!empty($images)) {
                $this->storeImages($vehicle, $images);
            }

            if (isset($data['features'])) {
                $vehicle->features()->delete();
                $this->storeFeatures($vehicle, $data['features']);
            }

            $this->clearVehicleCache($id);
            $this->clearListCache();

            return $vehicle->load(['images', 'features', 'availabilities', 'customPricings']);
        });
    }

    // =====================
    //     Delete Vehicle
    // =====================

    public function deleteVehicle(int $id): bool
    {
        return DB::transaction(function () use ($id) {

            $vehicle = Vehicle::findOrFail($id);

            foreach ($vehicle->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $vehicle->delete();

            $this->clearVehicleCache($id);
            $this->clearListCache();

            return true;
        });
    }

    // =====================
    //     Helpers
    // =====================

    private function storeImages(Vehicle $vehicle, array $images): void
    {
        $isFirst = $vehicle->images()->count() === 0;

        foreach ($images as $index => $file) {
            $path = $this->uplodeImage($file, 'vehicles');

            $vehicle->images()->create([
                'path'       => $path,
                'is_primary' => $isFirst && $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    private function storeFeatures(Vehicle $vehicle, array $features): void
    {
        foreach ($features as $feature) {
            $vehicle->features()->create([
                'feature_name'  => $feature['name'],
                'feature_value' => $feature['value'] ?? null,
            ]);
        }
    }

    private function storeAvailabilities(Vehicle $vehicle, array $availabilities): void
    {
        foreach ($availabilities as $availability) {
            $vehicle->availabilities()->create([
                'available_from' => $availability['available_from'],
                'available_to'   => $availability['available_to'],
                'is_blocked'     => $availability['is_blocked'] ?? false,
                'note'           => $availability['note'] ?? null,
            ]);
        }
    }

    private function storeCustomPricings(Vehicle $vehicle, array $pricings): void
    {
        foreach ($pricings as $pricing) {
            $vehicle->customPricings()->create([
                'date_from'    => $pricing['date_from'],
                'date_to'      => $pricing['date_to'],
                'custom_price' => $pricing['custom_price'],
                'reason'       => $pricing['reason'] ?? null,
            ]);
        }
    }

    // =====================
    //     Cache
    // =====================

    private function clearVehicleCache(int $id): void
    {
        Cache::forget(self::CACHE_PREFIX . ':show:' . $id);
    }

    private function clearListCache(): void
    {
        $version = Cache::get(self::CACHE_PREFIX . ':version', 0);
        Cache::put(self::CACHE_PREFIX . ':version', $version + 1, self::CACHE_TTL * 10);
    }

    private function getListVersion(): int
    {
        return Cache::get(self::CACHE_PREFIX . ':version', 0);
    }
}