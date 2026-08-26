<?php
// app/Repositories/Search/SearchQueryRepository.php

namespace App\Repositories\Search;

use App\Models\Airport;
use App\Models\Vehicle;
use App\Repositories\Search\Interfaces\SearchQueryRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchQueryRepository implements SearchQueryRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Base Query المشترك
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function baseGuestQuery(): Builder
    {
        $currentPriceSQL = "
            COALESCE(
                (
                    SELECT cp.price_per_day
                    FROM vehicle_custom_pricings cp
                    WHERE cp.vehicle_id = vehicles.id
                    AND NOW() BETWEEN cp.date_from AND cp.date_to
                    ORDER BY cp.date_from DESC
                    LIMIT 1
                ),
                vehicles.base_price_per_day
            )
        ";

        $allStarHostSQL = "
            EXISTS (
                SELECT 1 FROM hosts
                WHERE hosts.id = vehicles.host_id
                AND hosts.rating_avg >= 4.8
                AND hosts.total_trips >= 20
            )
        ";
        return Vehicle::query()
            ->with(['primaryImage', 'host'])
            ->select([
                'vehicles.id',
                'vehicles.make',
                'vehicles.model',
                'vehicles.year',
                'vehicles.city',
                'vehicles.base_price_per_day',
                'vehicles.rating_avg',
                'vehicles.total_bookings',
                'vehicles.listing_status',
                'vehicles.delivery_available',
                'vehicles.pickup_lat',
                'vehicles.pickup_lng',
                'vehicles.host_id',
            ])
            // ✅ نستخدم الـ Scopes مباشرة
            ->selectRaw("{$currentPriceSQL} as current_price")
            ->selectRaw("{$allStarHostSQL} as is_all_star_host")
            ->where('vehicles.listing_status', 'listed')
            ->where('vehicles.admin_review_status', 'approved');
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH GUEST
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchGuest(array $filters): CursorPaginator
    {
        $query   = $this->baseGuestQuery();
        $perPage = $filters['per_page'] ?? 5;

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // فلتر الموقع + الترتيب في مكان واحد
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if ($filters['search_type'] === 'location') {
            $lat    = (float) $filters['lat'];
            $lng    = (float) $filters['lng'];
            $radius = (float) ($filters['radius'] ?? 50);

            $haversine = "
                ROUND(
                    6371 * acos(
                        cos(radians({$lat})) * cos(radians(pickup_lat))
                        * cos(radians(pickup_lng) - radians({$lng}))
                        + sin(radians({$lat})) * sin(radians(pickup_lat))
                    ),
                2)
            ";

            $query->selectRaw("{$haversine} as distance")
            ->whereNotNull('vehicles.pickup_lat')
                ->whereNotNull('vehicles.pickup_lng')
                ->whereRaw("{$haversine} <= ?", [$radius])
                ->orderByDesc('vehicles.rating_avg')
                ->orderByDesc('vehicles.total_bookings')
                ->orderByDesc('vehicles.id');

        } else {
            $query->orderByRaw("
                    EXISTS (
                        SELECT 1 FROM hosts
                        WHERE hosts.id = vehicles.host_id
                        AND hosts.rating_avg >= 4.8
                        AND hosts.total_trips >= 20
                    ) DESC
                ")
                ->orderByDesc('vehicles.rating_avg')
                ->orderByDesc('vehicles.id');
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // فلتر التواريخ
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $this->applyDateFilter($query, $filters['date_from'], $filters['date_to']);
        }

        if (auth()->check()) {
            $this->saveRecentSearch($filters);
        }

        return $query->cursorPaginate($perPage);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // FILTER GUEST  (فلترة متقدمة)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function filterGuest(array $filters): CursorPaginator
    {
        $query   = $this->baseGuestQuery();
        $perPage = $filters['per_page'] ?? 5;

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 1. make + model أولاً (exact match)
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['make'])) {
            $query->where('vehicles.make','=' , $filters['make']);
        }
        if (!empty($filters['model'])) {
            $query->where('vehicles.model','=' , $filters['model']);
        }

        //return $query->cursorPaginate($perPage);

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 2. السعر BETWEEN حصراً
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
            $query->havingBetween('current_price', [
                $filters['min_price'],
                $filters['max_price'],
            ]);
        } elseif (!empty($filters['min_price'])) {
            $query->having('current_price', '>=', $filters['min_price']);
        } elseif (!empty($filters['max_price'])) {
            $query->having('current_price', '<=', $filters['max_price']);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 3. السنة BETWEEN حصراً
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['year_from']) && !empty($filters['year_to'])) {
            $query->whereBetween('vehicles.year', [
                $filters['year_from'],
                $filters['year_to'],
            ]);
        } elseif (!empty($filters['year_from'])) {
            $query->where('vehicles.year', '>=', $filters['year_from']);
        } elseif (!empty($filters['year_to'])) {
            $query->where('vehicles.year', '<=', $filters['year_to']);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 4. باقي فلاتر السيارة
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['fuel_type'])) {
            $query->where('vehicles.fuel_type', $filters['fuel_type']);
        }
        if (!empty($filters['transmission'])) {
            $query->where('vehicles.transmission', $filters['transmission']);
        }
        if (!empty($filters['seats'])) {
            $query->where('vehicles.seats', '>=', $filters['seats']);
        }
        if (!empty($filters['engine_capacity'])) {
            $query->where('vehicles.engine_capacity', '>=', $filters['engine_capacity']);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 5. الميزات (AND لكل ميزة - يجب توفر كلها)
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['feature_ids'])) {
            $query->whereHas('features', function ($q) use ($filters) {
                $q->whereIn('features.id', $filters['feature_ids']);
            });
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 6. التوصيل
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (isset($filters['delivery_available'])) {
            $query->where('vehicles.delivery_available', (bool) $filters['delivery_available']);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 7. التقييم
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['min_rating'])) {
            $query->where('vehicles.rating_avg', '>=', $filters['min_rating']);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 8. All-Star Host
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        if (!empty($filters['all_star_host'])) {
            $query->whereExists(function ($q) {
                $q->selectRaw(1)
                ->from('hosts')
                ->whereColumn('hosts.id', 'vehicles.host_id')
                ->where('hosts.rating_avg', '>=', 4.8)
                ->where('hosts.total_trips', '>=', 20);
            });
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 9. الترتيب
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        match ($filters['sort_by'] ?? 'rating_desc') {
            'price_asc'     => $query->orderByRaw('current_price ASC')
                                    ->orderByDesc('vehicles.id'),
            'price_desc'    => $query->orderByRaw('current_price DESC')
                                    ->orderByDesc('vehicles.id'),
            'bookings_desc' => $query->orderByDesc('vehicles.total_bookings')
                                    ->orderByDesc('vehicles.id'),
            default         => $query->orderByDesc('vehicles.rating_avg')
                                    ->orderByDesc('vehicles.id'),
        };

        return $query->cursorPaginate($perPage);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH HOST
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchHost(int $hostId, string $keyword, int $perPage, ?string $cursor): CursorPaginator
    {
        $currentPriceSQL = "
            COALESCE(
                (
                    SELECT cp.price_per_day
                    FROM vehicle_custom_pricings cp
                    WHERE cp.vehicle_id = vehicles.id
                    AND NOW() BETWEEN cp.date_from AND cp.date_to
                    ORDER BY cp.date_from DESC
                    LIMIT 1
                ),
                vehicles.base_price_per_day
            )
        ";

        return Vehicle::query()
            ->with(['primaryImage'])

            ->select([
                'id', 'make', 'model', 'year', 'city',
                'rating_avg',
                'total_bookings',
                'listing_status',
                'total_reviews',
                'admin_review_status',
                'delivery_available',
                'created_at',
            ])

            ->selectRaw("($currentPriceSQL) as current_price")

            ->where('host_id', $hostId)

            ->where(function ($q) use ($keyword) {
                $q->where('make', 'like', "%{$keyword}%")
                ->orWhere('model', 'like', "%{$keyword}%");
            })

            ->orderByDesc('created_at')
            ->orderByDesc('id')

            ->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Helpers
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function applyDateFilter($query, string $dateFrom, string $dateTo): void
    {
        $query->whereHas('availabilities', fn($q) =>
            $q->where('type', 'available')
                ->where('is_blocked', false)
                ->where('available_from', '<=', $dateFrom)
                ->where('available_to', '>=', $dateTo)
        )
        ->whereDoesntHave('availabilities', fn($q) =>
            $q->where('type', 'booking_block')
                ->where('available_from', '<=', $dateTo)
                ->where('available_to', '>=', $dateFrom)
        );
    }

    private function saveRecentSearch(array $filters): void
    {
        \App\Models\RecentSearch::create([
            'user_id'     => auth()->id(),
            'search_type' => $filters['search_type'],
            'lat'         => $filters['search_type'] === 'location' ? $filters['lat'] : null,
            'lng'         => $filters['search_type'] === 'location' ? $filters['lng'] : null,
            'searched_at' => now(),
        ]);

        // ── نحذف ما زاد عن 10 ────────────────────────────────
        $searches = \App\Models\RecentSearch::where('user_id', auth()->id())
            ->orderByDesc('searched_at')
            ->get();

        if ($searches->count() > 10) {
            $searches->slice(10)->each->delete();
        }

        \Illuminate\Support\Facades\Redis::del('inspired_searches:' . auth()->id());
    }
}
