<?php


namespace App\Repositories\Vehicle\Interfaces;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

interface VehicleQueryRepositoryInterface
{
    public function getTopVehiclesByCity(string $city, int $limit = 8): Collection;
    public function getTopVehiclesByDeliveryZone(string $zone, int $limit = 8): Collection;
    public function getTopVehiclesByAirport(string $airportName, int $limit = 8): Collection;
    public function getNearbyVehicles(float $lat, float $lng, int $limit = 10): Collection;
    public function getRecentSearches(int $userId, int $limit = 5): Collection;
    public function show(int $id): Vehicle;
}
