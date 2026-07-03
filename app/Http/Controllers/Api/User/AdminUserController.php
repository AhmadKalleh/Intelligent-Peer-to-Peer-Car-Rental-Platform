<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\FormRequestUserAdmin;
use App\Http\Resources\User\UserAdminResource;
use App\Services\User\Admin\UserAdminService;
use App\Services\User\Guest\UserImageService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminUserController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected UserAdminService  $_userAdminService,
        protected UserImageService  $_userImageService,
    ) {}

    // ─── Add User ──────────────────────────────────────────────────────────────

    public function addUser(FormRequestUserAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->addUser($request->validated());
            return $this->Success(
                new UserAdminResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Promote Guest → Host ──────────────────────────────────────────────────

    public function promoteGuestToHost(FormRequestUserAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->promoteGuestToHost($request->validated()['user_id']);
            return $this->Success(
                $result['data'] ? new UserAdminResource($result['data']) : [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Delete Guest ─────────────────────────────────────────────────────────

    public function deleteGuest(FormRequestUserAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->deleteGuest($request->validated()['user_id']);
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Delete Host ──────────────────────────────────────────────────────────

    public function deleteHost(FormRequestUserAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->deleteHost($request->validated()['user_id']);
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Toggle Guest Status (active ↔ banned) ────────────────────────────────

    public function toggleGuestStatus(FormRequestUserAdmin $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->toggleGuestStatus($request->validated()['user_id']);
            return $this->Success(
                $result['data'] ? new UserAdminResource($result['data']) : [],
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Get All Users ────────────────────────────────────────────────────────

    public function getUsers(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_userAdminService->getUsers(15);
            return $this->Success([
                'users'      => UserAdminResource::collection($result['data']),
                'pagination' => [
                    'current_page'  => $result['data']->currentPage(),
                    'last_page'     => $result['data']->lastPage(),
                    'per_page'      => $result['data']->perPage(),
                    'total'         => $result['data']->total(),
                    'next_page_url' => $result['data']->nextPageUrl(),
                    'prev_page_url' => $result['data']->previousPageUrl(),
                ],
            ], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── Update Profile Image (by Admin for any user) ─────────────────────────

    public function updateProfileImage(FormRequestUserAdmin $request): JsonResponse
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
}
