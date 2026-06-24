<?php


namespace App\Repositories\Vehicle;

use App\Repositories\Vehicle\Interfaces\VehicleQueryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\RecentSearch;
use App\Models\Vehicle;

class VehicelQueryRepository implements VehicleQueryRepositoryInterface
{
    private function baseVehicleQuery(): Builder
    {
        return Vehicle::query()
        ->with(['primaryImage', 'customPricings'])
        ->select([
            'vehicles.id',
            'vehicles.make',
            'vehicles.model',
            'vehicles.year',
            'vehicles.city',
            'vehicles.base_price_per_day',
            'vehicles.rating_avg',
            'vehicles.total_bookings',
            'vehicles.host_id',
            'vehicles.listing_status',
            'vehicles.admin_review_status',
        ])

        // 👇 السعر الفعلي
        ->withCurrentPrice()
        ->withAllStarHost()
        ->where('vehicles.listing_status', 'listed')
        ->where('vehicles.admin_review_status', 'approved');
    }


    private function applyPriorityOrder(Builder $query, int $limit): Collection
    {
        return $query
            ->orderByDesc('is_all_star_host')
            ->orderByDesc('vehicles.rating_avg')
            ->orderByDesc('vehicles.total_bookings')
            ->limit($limit)
            ->get();
    }


    public function getTopVehiclesByCity(string $city, int $limit = 8): Collection
    {
        $query = $this->baseVehicleQuery()
            ->where('vehicles.city', $city);

        return $this->applyPriorityOrder($query, $limit);
    }


    public function getTopVehiclesByDeliveryZone(string $zone, int $limit = 8): Collection
    {
        $query = $this->baseVehicleQuery()
            ->where('vehicles.delivery_available', true)
            ->where('vehicles.pickup_address', 'LIKE', '%' . $zone . '%');

        return $this->applyPriorityOrder($query, $limit);
    }


    public function getTopVehiclesByAirport(string $airportName, int $limit = 8): Collection
    {
        $query = $this->baseVehicleQuery()
            ->where('vehicles.pickup_address', 'LIKE', '%' . $airportName . '%');

        return $this->applyPriorityOrder($query, $limit);
    }

    public function getNearbyVehicles(float $lat, float $lng, int $limit = 10): Collection
    {
        return Vehicle::query()
            ->with(['primaryImage'])
            ->select([
                'id',
                'make',
                'model',
                'year',
                'city',
                'base_price_per_day',
                'rating_avg',
                'total_bookings',
                'pickup_lat',
                'pickup_lng',
                'listing_status',
                'admin_review_status',
            ])
            ->selectRaw(
                '(6371 * acos(
                    cos(radians(?)) * cos(radians(pickup_lat))
                    * cos(radians(pickup_lng) - radians(?))
                    + sin(radians(?)) * sin(radians(pickup_lat))
                )) AS distance',
                [$lat, $lng, $lat]
            )
            ->where('listing_status', 'listed')
            ->where('admin_review_status', 'approved')
            ->whereNotNull('pickup_lat')
            ->whereNotNull('pickup_lng')
            ->having('distance', '<=', 50)
            ->orderBy('distance', 'asc')
            ->orderByDesc('rating_avg')
            ->limit($limit)
            ->get();
    }


    public function getRecentSearches(int $userId, int $limit = 5): Collection
    {
        // ✅ أولاً: آخر 5 أبحاث فقط بناءً على searched_at
        $recentSearches = RecentSearch::query()
            ->where('user_id', $userId)
            ->orderByDesc('searched_at')
            ->limit($limit)
            ->get(['search_type', 'city', 'airport_code', 'lat', 'lng']);

        if ($recentSearches->isEmpty()) {
            return new Collection();
        }

        // ✅ ثانياً: لكل بحث نجلب سيارة واحدة مناسبة له
        $vehicles = new Collection();

        foreach ($recentSearches as $search) {
            $vehicle = Vehicle::query()
                ->with(['primaryImage'])
                ->select([
                    'id', 'make', 'model', 'year', 'city',
                    'base_price_per_day', 'rating_avg',
                    'total_bookings', 'listing_status',
                    'admin_review_status', 'pickup_lat', 'pickup_lng',
                ])
                ->where('listing_status', 'listed')
                ->where('admin_review_status', 'approved')
                ->when(
                    $search->search_type === 'anywhere',
                    fn($q) => $q
                )
                ->when(
                    $search->search_type === 'city',
                    fn($q) => $q->where('city', $search->city)
                )
                ->when(
                    $search->search_type === 'airport',
                    fn($q) => $q->whereRaw(
                        '(6371 * acos(
                            cos(radians(?)) * cos(radians(pickup_lat))
                            * cos(radians(pickup_lng) - radians(?))
                            + sin(radians(?)) * sin(radians(pickup_lat))
                        )) <= 20',
                        [$search->lat, $search->lng, $search->lat]
                    )
                )
                ->when(
                    $search->search_type === 'current_location',
                    fn($q) => $q->whereRaw(
                        '(6371 * acos(
                            cos(radians(?)) * cos(radians(pickup_lat))
                            * cos(radians(pickup_lng) - radians(?))
                            + sin(radians(?)) * sin(radians(pickup_lat))
                        )) <= 50',
                        [$search->lat, $search->lng, $search->lat]
                    )
                )
                ->orderByDesc('rating_avg')
                ->first(); // ✅ سيارة واحدة لكل بحث

            if ($vehicle) {
                $vehicles->push($vehicle);
            }
        }

        return $vehicles;
    }

    public function show(int $id): Vehicle
    {
        return Vehicle::query()
            ->with([
                // ─── الفقرة الأولى: تفاصيل السيارة ──────────────
                'images'         => fn($q) => $q->orderBy('sort_order'),
                'features',
                'availabilities' => fn($q) => $q
                    ->where('available_from', '>=', now()->toDateString())
                    ->where('is_blocked', false),
                'customPricings' => fn($q) => $q
                    ->where('date_to', '>=', now()->toDateString()),

                // ─── الفقرة الثانية: Host ─────────────────────────
                'host.user.image',

                // ─── الفقرة الثالثة: Reviews ──────────────────────
                'reviews' => fn($q) => $q
                    ->where('is_visible', true)
                    ->with('guest:id,full_name')
                    ->select([
                        'id',
                        'vehicle_id',
                        'host_id',
                        'user_id',
                        'booking_id',
                        'overall_rating',
                        'cleanliness_rating',
                        'maintenance_rating',
                        'comfort_rating',
                        'communication_rating',
                        'punctuality_rating',
                        'comment',
                        'created_at',
                    ]),
            ])
            ->selectRaw('vehicles.*')
            ->withCurrentPrice()
            ->withAllStarHost()
            ->findOrFail($id);
    }
}
