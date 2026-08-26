<?php
// app/Http/Controllers/Api/Search/AdminSearchController.php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequests\FormRequestAdminSearch;
use App\Http\Resources\User\UserAdminResource;
use App\Services\Search\SearchAdminService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminSearchController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected SearchAdminService $_searchAdminService
    ) {}

    // ─── GET /api/admin/search/users ──────────────────────────
    public function searchUsers(FormRequestAdminSearch $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_searchAdminService->searchUsers($request->validated());

            return $this->Success([
                'users'      => UserAdminResource::collection($result['data']),
                'pagination' => [
                    'next_cursor' => $result['data']->nextCursor()?->encode(),
                    'prev_cursor' => $result['data']->previousCursor()?->encode(),
                    'per_page'    => $result['data']->perPage(),
                    'has_more'    => $result['data']->hasMorePages(),
                ],
            ], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
