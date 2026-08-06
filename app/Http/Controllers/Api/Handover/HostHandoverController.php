<?php
// app/Http/Controllers/Api/Handover/HostHandoverController.php

namespace App\Http\Controllers\Api\Handover;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandoverRequests\FormRequestHostHandover;
use App\Services\Handover\HandoverHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostHandoverController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected HandoverHostService $_handoverHostService
    ) {}

    // ─── POST /api/host/bookings/handover/generate ────────────
    public function generate(FormRequestHostHandover $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_handoverHostService->generate(
                $request->validated()['booking_id'],
                $hostId
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
