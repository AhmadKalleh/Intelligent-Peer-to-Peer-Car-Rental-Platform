<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\FormRequestUserGuest;
use App\Http\Resources\User\GuestDetailResource;
use App\Services\User\Guest\UserGuestService;
use App\Services\User\Guest\UserImageService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestUserController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected UserImageService $_userImageService,
        protected UserGuestService $_userGuestService,
    ) {}

    // ─── Update Guest Profile Image ───────────────────────────────────────────

    public function updateProfileImage(FormRequestUserGuest $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userImageService->updateProfileImage(
                auth()->id(),
                $request->file('image')
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Show Guest Details ───────────────────────────────────────────────────

    public function showGuestDetails(FormRequestUserGuest $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userGuestService->showGuestDetails(auth()->id());
            return $this->Success(
                new GuestDetailResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Change Guest Password ─────────────────────────────────────────────────

    public function changeGuestPassword(FormRequestUserGuest $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userGuestService->changeGuestPassword(
                auth()->id(),
                $request->validated()
            );
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
