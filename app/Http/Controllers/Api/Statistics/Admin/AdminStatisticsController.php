<?php
// app/Http/Controllers/Api/Statistics/Admin/AdminStatisticsController.php

namespace App\Http\Controllers\Api\Statistics\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\Admin\AdminStatisticsService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminStatisticsController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected AdminStatisticsService $_statisticsService
    ) {}

    // ─── GET /api/admin/statistics ────────────────────────────
    public function index(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_statisticsService->getAllStatistics();
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
