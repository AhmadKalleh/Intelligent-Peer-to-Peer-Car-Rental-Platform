<?php


namespace App\Services\Vehicle;

use App\Http\Resources\Vehicle\VehicleGuestListResource;
use App\Repositories\Vehicle\Interfaces\VehicleQueryRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class VehicleQueryService
{
    public function __construct(
        protected VehicleQueryRepositoryInterface $_vehicleQueryRepository
    ) {}

    public function getCitiesData(): array
    {
        $cacheKey   = 'home_section:cities';
        $cached     = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        // نجلب كـ Collection ثم نحوّل لـ Resource مباشرة
        $data = [
            'damascus' => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByCity('Damascus')
            )->resolve(),

            'aleppo'   => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByCity('Aleppo')
            )->resolve(),

            'homs'     => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByCity('Homs')
            )->resolve(),
        ];

        Redis::setex($cacheKey, 600, json_encode($data));

        return $data;
    }


    public function getDeliveryData(): array
    {
        $cacheKey = 'home_section:delivery';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = [
            'damascus_kafr_souseh' => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByDeliveryZone('Kafr Souseh')
            )->resolve(),

            'homs_al_inshaat'      => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByDeliveryZone('Inshaat')
            )->resolve(),

            'aleppo_shahbaa'       => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByDeliveryZone('Shahbaa')
            )->resolve(),
        ];

        Redis::setex($cacheKey, 600, json_encode($data));

        return $data;
    }


    public function getAirportsData(): array
    {
        $cacheKey = 'home_section:airports';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = [
            'damascus_airport' => VehicleGuestListResource::collection(
                $this->_vehicleQueryRepository->getTopVehiclesByAirport('Damascus International Airport')
            )->resolve(),
        ];

        Redis::setex($cacheKey, 600, json_encode($data));

        return $data;
    }


    public function getNearbyData(array $data, ?array $user): array
    {
        $identifier  = $user ? "user:{$user['id']}" : 'guest:' . md5(request()->ip());
        $locationKey = "geo:{$identifier}";
        $dataKey     = "nearby:{$identifier}";

        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;

        if ($lat && $lng) {
            // موقع جديد → احفظه وامسح كاش البيانات القديمة
            Redis::setex($locationKey, 600, json_encode(['lat' => $lat, 'lng' => $lng]));
            Redis::del($dataKey);
        } else {
            // حاول استرجاع الموقع المحفوظ
            $cachedGeo = Redis::get($locationKey);

            if (!$cachedGeo) {
                return ['status' => 'location_required'];
            }

            $geo = json_decode($cachedGeo, true);
            $lat = $geo['lat'];
            $lng = $geo['lng'];
        }

        // كاش البيانات
        $cachedData = Redis::get($dataKey);

        if ($cachedData) {
            return ['status' => 'success', 'data' => json_decode($cachedData, true)];
        }

        $vehicles = VehicleGuestListResource::collection(
            $this->_vehicleQueryRepository->getNearbyVehicles($lat, $lng)
        )->resolve();

        Redis::setex($dataKey, 600, json_encode($vehicles));

        return ['status' => 'success', 'data' => $vehicles];
    }


    public function resetLocation(?array $user): void
    {
        $identifier  = $user ? "user:{$user['id']}" : 'guest:' . md5(request()->ip());
        Redis::del("geo:{$identifier}", "nearby:{$identifier}");
    }


    private function getRecentSearches(int $userId): array
    {
        $cacheKey = "inspired_searches:{$userId}";
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $vehicles = VehicleGuestListResource::collection(
            $this->_vehicleQueryRepository->getRecentSearches($userId)
        )->resolve();

        Redis::setex($cacheKey, 300, json_encode($vehicles));

        return $vehicles;
    }

    public function getAllHomeData(?array $user): array
    {
        $recentSearches = $user
            ? $this->_vehicleQueryRepository->getRecentSearches($user['id'])->toArray()
            : [];

        return [
            'inspired_by_recent_searches' => $user
                        ? $this->getRecentSearches($user['id'])
                        : [],
            'cities'                      => $this->getCitiesData(),
            'delivery'                    => $this->getDeliveryData(),
            'airports'                    => $this->getAirportsData(),
        ];
    }

    public function show(int $id): array
    {
        $vehicle = $this->_vehicleQueryRepository->show($id);

        return [
            'data'    => $vehicle,
            'message' => 'Vehicle details retrieved successfully.',
            'code'    => 200,
        ];
    }
}
