<?php

namespace App\Http\Controllers\Api\Complaint;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintRequests\FormRequestComplaintAdmin;
use App\Http\Resources\Complaint\ComplaintResource;
use App\Http\Resources\Complaint\FlaggedUserResource;
use App\Services\Complaint\Admin\ComplaintAdminService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminComplaintController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected ComplaintAdminService $_complaintAdminService
    ) {}

    // ─── جميع الشكاوي مفرزة: غير مردود عليها / مردود عليها / مستخدمون متكررون ──

    public function getComplaints(): JsonResponse
    {
        $data = [];
        try {
            $result    = $this->_complaintAdminService->getComplaintsOverview(15, 5);
            $overview  = $result['data'];
            $unanswered = $overview['unanswered'];
            $answered   = $overview['answered'];

            return $this->Success([
                'unanswered_complaints' => [
                    'data'       => ComplaintResource::collection($unanswered),
                    'pagination' => [
                        'current_page'  => $unanswered->currentPage(),
                        'last_page'     => $unanswered->lastPage(),
                        'per_page'      => $unanswered->perPage(),
                        'total'         => $unanswered->total(),
                        'next_page_url' => $unanswered->nextPageUrl(),
                        'prev_page_url' => $unanswered->previousPageUrl(),
                    ],
                ],
                'answered_complaints' => [
                    'data'       => ComplaintResource::collection($answered),
                    'pagination' => [
                        'current_page'  => $answered->currentPage(),
                        'last_page'     => $answered->lastPage(),
                        'per_page'      => $answered->perPage(),
                        'total'         => $answered->total(),
                        'next_page_url' => $answered->nextPageUrl(),
                        'prev_page_url' => $answered->previousPageUrl(),
                    ],
                ],
                'flagged_users' => FlaggedUserResource::collection($overview['flagged_users']),
            ], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── الرد على شكوى ─────────────────────────────────────────────────────────

    public function replyToComplaint(FormRequestComplaintAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            $result    = $this->_complaintAdminService->replyToComplaint(
                $validated['complaint_id'],
                $validated['admin_reply'],
                auth()->id()
            );
            return $this->Success(
                $result['data'] ? new ComplaintResource($result['data']) : [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── عدد الشكاوي الغير مردود عليها ──────────────────────────────────────────

    public function countUnanswered(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_complaintAdminService->countUnanswered();
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
