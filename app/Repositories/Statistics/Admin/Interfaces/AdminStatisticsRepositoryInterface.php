<?php
// app/Repositories/Statistics/Admin/Interfaces/AdminStatisticsRepositoryInterface.php

namespace App\Repositories\Statistics\Admin\Interfaces;

interface AdminStatisticsRepositoryInterface
{
    public function getUsersStats(): array;
    public function getVehiclesStats(): array;
    public function getBookingsStats(): array;
    public function getRevenueStats(): array;
    public function getBookingsStatistics(): array;
    public function getRevenueStatistics(): array;
    public function getTopPerformers(): array;
}
