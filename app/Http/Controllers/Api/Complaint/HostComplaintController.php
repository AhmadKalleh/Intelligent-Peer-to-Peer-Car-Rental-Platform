<?php

namespace App\Http\Controllers\Api\Complaint;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintRequests\FormRequestComplaintHost;
use App\Http\Resources\Complaint\ComplaintResource;
use App\Services\Complaint\ComplaintReasonService;
use App\Services\Complaint\ComplaintService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostComplaintController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected ComplaintService       $_complaintService,
        protected ComplaintReasonService $_complaintReasonService,
    ) {}

    // ─── إرسال شكوى من الهوست ───────────────────────────────────────────────────

    public function submitComplaint(FormRequestComplaintHost $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_complaintService->submitComplaint(auth()->id(), $request->validated());
            return $this->Success(
                $result['data'] ? new ComplaintResource($result['data']) : [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── اللائحة الثابتة لأسباب الشكوى ──────────────────────────────────────────

    public function getComplaintReasons(): JsonResponse
    {
        $data = [];
        try {
            $reasons = $this->_complaintReasonService->getReasons();
            return $this->Success($reasons, 'تم استرجاع أسباب الشكوى بنجاح.', 200);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
