<?php
// app/Services/Statistics/Admin/AdminStatisticsService.php

namespace App\Services\Statistics\Admin;

use App\Repositories\Statistics\Admin\Interfaces\AdminStatisticsRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class AdminStatisticsService
{
    public function __construct(
        protected AdminStatisticsRepositoryInterface $_statisticsRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // GET ALL STATISTICS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getAllStatistics(): array
    {
        return [
            'data' => [
                'overview'         => $this->getOverview(),
                'monthly_bookings' => $this->getMonthlyBookings(),
                'monthly_revenue'  => $this->getMonthlyRevenue(),
                'top_performers'   => $this->getTopPerformers(),
            ],
            'message' => 'Statistics retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // OVERVIEW (كاش 5 دقائق)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function getOverview(): array
    {
        $cacheKey = 'admin:stats:overview';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = [
            'users'    => $this->_statisticsRepository->getUsersStats(),
            'vehicles' => $this->_statisticsRepository->getVehiclesStats(),
            'bookings' => $this->_statisticsRepository->getBookingsStats(),
            'revenue'  => $this->_statisticsRepository->getRevenueStats(),
        ];

        Redis::setex($cacheKey, 300, json_encode($data));

        return $data;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MONTHLY BOOKINGS (كاش ساعة)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function getMonthlyBookings(): array
    {
        $cacheKey = 'admin:stats:monthly_bookings';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = $this->_statisticsRepository->getMonthlyBookings();
        Redis::setex($cacheKey, 3600, json_encode($data));

        return $data;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MONTHLY REVENUE (كاش ساعة)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function getMonthlyRevenue(): array
    {
        $cacheKey = 'admin:stats:monthly_revenue';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = $this->_statisticsRepository->getMonthlyRevenue();
        Redis::setex($cacheKey, 3600, json_encode($data));

        return $data;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TOP PERFORMERS (كاش 30 دقيقة)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function getTopPerformers(): array
    {
        $cacheKey = 'admin:stats:top_performers';
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $data = $this->_statisticsRepository->getTopPerformers();
        Redis::setex($cacheKey, 1800, json_encode($data));

        return $data;
    }
}
