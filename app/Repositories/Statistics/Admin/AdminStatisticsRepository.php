<?php
// app/Repositories/Statistics/Admin/AdminStatisticsRepository.php

namespace App\Repositories\Statistics\Admin;

use App\Models\Booking;
use App\Models\Host;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Statistics\Admin\Interfaces\AdminStatisticsRepositoryInterface;

use Illuminate\Support\Facades\Storage ;

class AdminStatisticsRepository implements AdminStatisticsRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // USERS STATS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getUsersStats(): array
    {
        $totalUsers  = User::count();
        $totalHosts  = Host::count();
        $totalGuests = User::whereHas('roles', fn($q) =>
            $q->where('name', 'guest')
        )->count();

        $newThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $newLastMonth = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        return [
            'total_users'     => $totalUsers,
            'total_hosts'     => $totalHosts,
            'total_guests'    => $totalGuests,
            'new_this_month'  => $newThisMonth,
            'new_last_month'  => $newLastMonth,
            'growth_rate'     => $newLastMonth > 0
                ? round((($newThisMonth - $newLastMonth) / $newLastMonth) * 100, 1)
                : 0,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // VEHICLES STATS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getVehiclesStats(): array
    {
        $stats = Vehicle::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN listing_status = 'listed' AND admin_review_status = 'approved' THEN 1 ELSE 0 END) as listed,
                SUM(CASE WHEN admin_review_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN admin_review_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN listing_status = 'snoozed' THEN 1 ELSE 0 END) as snoozed,
                SUM(CASE WHEN listing_status = 'unlisted' THEN 1 ELSE 0 END) as unlisted
            ")
            ->first();

        return [
            'total'    => (int) $stats->total,
            'listed'   => (int) $stats->listed,
            'pending'  => (int) $stats->pending,
            'rejected' => (int) $stats->rejected,
            'snoozed'  => (int) $stats->snoozed,
            'unlisted' => (int) $stats->unlisted,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // BOOKINGS STATS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getBookingsStats(): array
    {
        $stats = Booking::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'active'    THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                ROUND(AVG(total_days), 1) as avg_days,
                ROUND(AVG(total_amount), 2) as avg_amount
            ")
            ->first();

        $total             = (int) $stats->total;
        $completedCount    = (int) $stats->completed;
        $cancelledCount    = (int) $stats->cancelled;

        return [
            'total'           => $total,
            'pending'         => (int) $stats->pending,
            'confirmed'       => (int) $stats->confirmed,
            'active'          => (int) $stats->active,
            'completed'       => $completedCount,
            'cancelled'       => $cancelledCount,
            'avg_days'        => (float) $stats->avg_days,
            'avg_amount'      => (float) $stats->avg_amount,
            'completion_rate' => $total > 0
                ? round(($completedCount / $total) * 100, 1)
                : 0,
            'cancellation_rate' => $total > 0
                ? round(($cancelledCount / $total) * 100, 1)
                : 0,
        ];
    }

    public function getYearlyBookings(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = Booking::query()
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') as period,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $result = [];

        $period = $start->copy();

        while ($period <= $end) {
            $key = $period->format('Y-m');

            $row = $rows->get($key);

            $result[] = [
                'period'    => $key,
                'total'     => $row ? (int) $row->total : 0,
                'completed' => $row ? (int) $row->completed : 0,
                'cancelled' => $row ? (int) $row->cancelled : 0,
            ];

            $period->addMonth();
        }

        return $result;
    }
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // REVENUE STATS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getRevenueStats(): array
    {
        $allTime = Booking::where('status', 'completed')
            ->selectRaw("
                ROUND(SUM(platform_fee), 2)    as total_platform_fee,
                ROUND(SUM(total_amount), 2)    as total_revenue,
                ROUND(SUM(discount_amount), 2) as total_discounts,
                ROUND(SUM(delivery_fee), 2)    as total_delivery_fees
            ")
            ->first();

        $thisMonth = Booking::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('platform_fee');

        $lastMonth = Booking::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('platform_fee');

        $thisWeek = Booking::where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('platform_fee');

        $today = Booking::where('status', 'completed')
            ->whereDate('created_at', now()->toDateString())
            ->sum('platform_fee');

        return [
            'total_platform_fee'  => (float) $allTime->total_platform_fee,
            'total_revenue'       => (float) $allTime->total_revenue,
            'total_discounts'     => (float) $allTime->total_discounts,
            'total_delivery_fees' => (float) $allTime->total_delivery_fees,
            'this_month'          => (float) $thisMonth,
            'last_month'          => (float) $lastMonth,
            'this_week'           => (float) $thisWeek,
            'today'               => (float) $today,
            'month_growth_rate'   => $lastMonth > 0
                ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
                : 0,
        ];
    }

    public function getMonthlyBookings(): array
    {
        $start = now()->subWeeks(3)->startOfWeek();
        $end   = now()->endOfWeek();

        $data = Booking::query()
            ->selectRaw("
                YEAR(created_at) as year,
                WEEK(created_at, 1) as week,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("YEAR(created_at), WEEK(created_at, 1)")
            ->get()
            ->keyBy(function ($row) {
                return sprintf(
                    '%d-W%02d',
                    $row->year,
                    $row->week
                );
            });

        $result = [];

        for ($i = 0; $i < 4; $i++) {

            $weekStart = now()
                ->subWeeks(3 - $i)
                ->startOfWeek();

            $weekNumber = $weekStart->weekOfYear;

            $period = sprintf(
                '%d-W%02d',
                $weekStart->year,
                $weekNumber
            );

            $row = $data->get($period);

            $result[] = [
                'period'    => $period,
                'total'     => (int) ($row->total ?? 0),
                'completed' => (int) ($row->completed ?? 0),
                'cancelled' => (int) ($row->cancelled ?? 0),
            ];
        }

        return $result;
    }

    public function getWeeklyBookings(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end   = now()->endOfDay();

        $data = Booking::query()
            ->selectRaw("
                DATE(created_at) as period,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("DATE(created_at)")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $result = [];

        for ($i = 0; $i < 7; $i++) {

            $date = now()
                ->subDays(6 - $i)
                ->toDateString();

            $row = $data->get($date);

            $result[] = [
                'period'    => $date,
                'total'     => (int) ($row->total ?? 0),
                'completed' => (int) ($row->completed ?? 0),
                'cancelled' => (int) ($row->cancelled ?? 0),
            ];
        }

        return $result;
    }


    public function getDailyBookings(): array
    {
        $row = Booking::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->whereDate('created_at', today())
            ->first();

        return [
            [
                'period'    => today()->toDateString(),
                'total'     => (int) ($row->total ?? 0),
                'completed' => (int) ($row->completed ?? 0),
                'cancelled' => (int) ($row->cancelled ?? 0),
            ]
        ];
    }

    public function getBookingsStatistics(): array
    {
        return [
            'yearly'  => $this->getYearlyBookings(),
            'monthly' => $this->getMonthlyBookings(),
            'weekly'  => $this->getWeeklyBookings(),
            'daily'   => $this->getDailyBookings(),
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // REVENUE STATISTICS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public function getRevenueStatistics(): array
    {
        return [
            'yearly'  => $this->getYearlyRevenue(),
            'monthly' => $this->getMonthlyRevenue(),
            'weekly'  => $this->getWeeklyRevenue(),
            'daily'   => $this->getDailyRevenue(),
        ];
    }

    public function getYearlyRevenue(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = Booking::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') as period,
                ROUND(SUM(platform_fee), 2) as platform_fee,
                ROUND(SUM(total_amount), 2) as total_amount,
                ROUND(SUM(discount_amount), 2) as discounts
            ")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $result = [];

        $period = $start->copy();

        while ($period <= $end) {
            $key = $period->format('Y-m');

            $row = $rows->get($key);

            $result[] = [
                'period'       => $key,
                'platform_fee' => $row ? (float) $row->platform_fee : 0.0,
                'total_amount' => $row ? (float) $row->total_amount : 0.0,
                'discounts'    => $row ? (float) $row->discounts : 0.0,
            ];

            $period->addMonth();
        }

        return $result;
    }


    public function getMonthlyRevenue(): array
    {
        $start = now()->subWeeks(3)->startOfWeek();
        $end = now()->endOfWeek();

        $rows = Booking::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                YEAR(created_at) as year,
                WEEK(created_at, 1) as week,
                ROUND(SUM(platform_fee), 2) as platform_fee,
                ROUND(SUM(total_amount), 2) as total_amount,
                ROUND(SUM(discount_amount), 2) as discounts
            ")
            ->groupByRaw("YEAR(created_at), WEEK(created_at, 1)")
            ->orderByRaw("YEAR(created_at), WEEK(created_at, 1)")
            ->get()
            ->keyBy(function ($row) {
                return sprintf(
                    '%d-W%02d',
                    $row->year,
                    $row->week
                );
            });

        $result = [];

        $period = $start->copy();

        while ($period <= $end) {
            $key = sprintf(
                '%d-W%02d',
                $period->isoWeekYear,
                $period->isoWeek
            );

            $row = $rows->get($key);

            $result[] = [
                'period'       => $key,
                'platform_fee' => $row ? (float) $row->platform_fee : 0.0,
                'total_amount' => $row ? (float) $row->total_amount : 0.0,
                'discounts'    => $row ? (float) $row->discounts : 0.0,
            ];

            $period->addWeek();
        }

        return $result;
    }


    public function getWeeklyRevenue(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rows = Booking::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                DATE(created_at) as period,
                ROUND(SUM(platform_fee), 2) as platform_fee,
                ROUND(SUM(total_amount), 2) as total_amount,
                ROUND(SUM(discount_amount), 2) as discounts
            ")
            ->groupByRaw("DATE(created_at)")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $result = [];

        $period = $start->copy();

        while ($period <= $end) {
            $key = $period->format('Y-m-d');

            $row = $rows->get($key);

            $result[] = [
                'period'       => $key,
                'platform_fee' => $row ? (float) $row->platform_fee : 0.0,
                'total_amount' => $row ? (float) $row->total_amount : 0.0,
                'discounts'    => $row ? (float) $row->discounts : 0.0,
            ];

            $period->addDay();
        }

        return $result;
    }

    public function getDailyRevenue(): array
    {
        $row = Booking::query()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->selectRaw("
                ROUND(COALESCE(SUM(platform_fee), 0), 2) as platform_fee,
                ROUND(COALESCE(SUM(total_amount), 0), 2) as total_amount,
                ROUND(COALESCE(SUM(discount_amount), 0), 2) as discounts
            ")
            ->first();

        return [
            [
                'period'       => today()->toDateString(),
                'platform_fee' => (float) $row->platform_fee,
                'total_amount' => (float) $row->total_amount,
                'discounts'    => (float) $row->discounts,
            ]
        ];
    }
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TOP PERFORMERS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getTopPerformers(): array
    {
        // ── أفضل 5 سيارات ─────────────────────────────────
        $topVehicles = Vehicle::query()
            ->with(['primaryImage'])
            ->select(['id', 'make', 'model', 'year', 'total_bookings', 'rating_avg'])
            ->where('admin_review_status', 'approved')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get()
            ->map(fn($v) => [
                'id'             => $v->id,
                'name'           => "{$v->make} {$v->model} {$v->year}",
                'total_bookings' => $v->total_bookings,
                'rating_avg'     => (float) ($v->rating_avg ?? 0),
                'image'          => $v->primaryImage
                    ? url(Storage::url($v->primaryImage->path))
                    : null,
            ]);

        // ── أفضل 5 هوست ───────────────────────────────────
        $topHosts = Host::query()
            ->with(['user:id,full_name'])
            ->select(['id', 'user_id', 'total_earnings', 'total_trips', 'rating_avg'])
            ->orderByDesc('total_earnings')
            ->limit(5)
            ->get()
            ->map(fn($h) => [
                'id'             => $h->id,
                'name'           => $h->user->full_name ?? null,
                'total_earnings' => (float) $h->total_earnings,
                'total_trips'    => (int) $h->total_trips,
                'rating_avg'     => (float) ($h->rating_avg ?? 0),
            ]);

        // ── أكثر المدن طلباً ──────────────────────────────
        $topCities = Booking::query()
            ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
            ->where('bookings.status', 'completed')
            ->selectRaw('vehicles.city, COUNT(*) as total_bookings')
            ->whereNotNull('vehicles.city')
            ->groupBy('vehicles.city')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'city'           => $row->city,
                'total_bookings' => (int) $row->total_bookings,
            ]);

        // ── أكثر أنواع السيارات طلباً ─────────────────────
        $topMakes = Booking::query()
            ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
            ->where('bookings.status', 'completed')
            ->selectRaw('vehicles.make, COUNT(*) as total_bookings')
            ->groupBy('vehicles.make')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'make'           => $row->make,
                'total_bookings' => (int) $row->total_bookings,
            ]);

        return [
            'top_vehicles' => $topVehicles,
            'top_hosts'    => $topHosts,
            'top_cities'   => $topCities,
            'top_makes'    => $topMakes,
        ];
    }
}
