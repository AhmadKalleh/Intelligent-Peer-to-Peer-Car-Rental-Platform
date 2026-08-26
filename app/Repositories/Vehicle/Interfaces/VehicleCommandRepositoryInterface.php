<?php


namespace App\Repositories\Vehicle\Interfaces;
use App\Models\Vehicle;
use App\Models\VehicleCustomPricing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VehicleCommandRepositoryInterface
{
    public function store(array $data, bool $isFirstTime): array;
    public function getHostVehicles(int $hostId, string $status, int $perPage): LengthAwarePaginator;
    public function showForHost(int $vehicleId, int $hostId): Vehicle;
    public function updateBasicInfo(int $vehicleId, int $hostId, array $data): Vehicle;
    public function updateListingStatus(int $vehicleId, int $hostId, string $status): array;

    // ─── Snooze ──────────────────────────────────────────
    public function storeSnooze(int $vehicleId, int $hostId, array $data): array;

    // ─── Pricing ─────────────────────────────────────────
    public function updatePricing(int $vehicleId, int $hostId, array $data): array;
    public function storeCustomPricing(int $vehicleId, int $hostId, array $data): array;
    public function updateCustomPricing(int $vehicleId, int $hostId, int $pricingId, array $data): array;
    public function destroyCustomPricing(int $vehicleId, int $hostId, int $pricingId): bool;

    // Images
    public function uploadImages(int $vehicleId, int $hostId, array $data): bool;
    public function destroyImage(int $vehicleId, int $hostId, int $imageId): bool;
    public function setPrimaryImage(int $vehicleId, int $hostId, int $imageId): bool;


    // Features
    public function syncFeatures(int $vehicleId, int $hostId, array $featureIds): array;

    // Availabilities
    public function updateAvailability(int $vehicleId, int $hostId, array $data): array;


    // Location
    public function updateLocation(int $vehicleId, int $hostId, array $data): array;

}
