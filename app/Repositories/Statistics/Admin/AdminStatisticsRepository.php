<?php
// app/Repositories/Statistics/Admin/AdminStatisticsRepository.php

namespace App\Repositories\Statistics\Admin;

use App\Models\Booking;
use App\Models\Host;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Statistics\Admin\Interfaces\AdminStatisticsRepositoryInterface;


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

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MONTHLY BOOKINGS (آخر 12 شهر)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getMonthlyBookings(): array
    {
        $data = Booking::query()
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'asc')
            ->get();

        return $data->map(fn($row) => [
            'month'     => $row->month,
            'total'     => (int) $row->total,
            'completed' => (int) $row->completed,
            'cancelled' => (int) $row->cancelled,
        ])->toArray();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // MONTHLY REVENUE (آخر 12 شهر)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getMonthlyRevenue(): array
    {
        $data = Booking::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') as month,
                ROUND(SUM(platform_fee), 2)    as platform_fee,
                ROUND(SUM(total_amount), 2)    as total_amount,
                ROUND(SUM(discount_amount), 2) as discounts
            ")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'asc')
            ->get();

        return $data->map(fn($row) => [
            'month'        => $row->month,
            'platform_fee' => (float) $row->platform_fee,
            'total_amount' => (float) $row->total_amount,
            'discounts'    => (float) $row->discounts,
        ])->toArray();
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
                    ? asset('storage/' . $v->primaryImage->path)
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
