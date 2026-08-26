<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\FormRequestUserHost;
use App\Http\Resources\User\HostDetailResource;
use App\Services\User\Guest\UserImageService;
use App\Services\User\Host\UserHostService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostUserController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected UserHostService  $_userHostService,
        protected UserImageService $_userImageService,
    ) {}

    // ─── Show Host Details ────────────────────────────────────────────────────

    public function showHostDetails(FormRequestUserHost $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userHostService->showHostDetails($request->validated()['host_id']);
            return $this->Success(
                new HostDetailResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Change Host Password (Admin only) ───────────────────────────────────

    public function changeHostPassword(FormRequestUserHost $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            $result    = $this->_userHostService->changeHostPassword(
                $validated['user_id'],
                $validated
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Update Host Profile Image ────────────────────────────────────────────

    public function updateProfileImage(FormRequestUserHost $request): JsonResponse
    {
        $data = [];
        try {
            $validated = $request->validated();
            $result    = $this->_userImageService->updateProfileImage(
                $validated['user_id'],
                $request->file('image')
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Get Host ID (للمستخدم المسجّل دخوله حاليًا) ← جديد ───────────────────
    public function getHostId(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userHostService->getHostId(auth()->id());
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
