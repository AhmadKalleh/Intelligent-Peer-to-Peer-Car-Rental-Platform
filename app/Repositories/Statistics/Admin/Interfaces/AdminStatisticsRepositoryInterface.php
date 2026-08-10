<?php
// app/Repositories/Statistics/Admin/Interfaces/AdminStatisticsRepositoryInterface.php

namespace App\Repositories\Statistics\Admin\Interfaces;

interface AdminStatisticsRepositoryInterface
{
    public function getUsersStats(): array;
    public function getVehiclesStats(): array;
    public function getBookingsStats(): array;
    public function getRevenueStats(): array;
    public function getMonthlyBookings(): array;
    public function getMonthlyRevenue(): array;
    public function getTopPerformers(): array;
}
