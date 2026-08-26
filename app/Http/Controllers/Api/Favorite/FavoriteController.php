<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteRequests\FormRequestFavorite;
use App\Services\Favorite\FavoriteService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FavoriteController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected FavoriteService $_favoriteService
    ) {}

    // ─────────────────────────────────────────────────────────
    //  LISTS
    // ─────────────────────────────────────────────────────────

    /**
     * POST /api/favorites/lists
     * إنشاء ليستا مفضلة جديدة
     */
    public function createList(FormRequestFavorite $request): JsonResponse
    {
        try {
            $result = $this->_favoriteService->createList(
                $request->user()->id,
                $request->validated()['name']
            );

            return $this->Success($result, 'Favorite list created successfully.', 201);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/favorites/lists
     * جلب كل ليستات المستخدم
     */
    public function getAllLists(Request $request): JsonResponse
    {
        try {
            $result = $this->_favoriteService->getAllLists($request->user()->id);

            return $this->Success($result, 'Favorite lists retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/favorites/lists/show?list_id=1
     * جلب ليستا واحدة مع سياراتها وحالتها المحدّثة تلقائياً
     */
    public function getList(FormRequestFavorite $request): JsonResponse
    {
        try {
            $result = $this->_favoriteService->getListWithVehicles(
                $request->validated()['list_id'],
                $request->user()->id
            );

            if (empty($result)) {
                return $this->Error([], 'Favorite list not found.', 404);
            }

            return $this->Success($result, 'Favorite list retrieved successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/favorites/lists/rename
     * تعديل اسم الليستا
     */
    public function renameList(FormRequestFavorite $request): JsonResponse
    {
        try {
            $data    = $request->validated();
            $renamed = $this->_favoriteService->renameList(
                $data['list_id'],
                $request->user()->id,
                $data['name']
            );

            if (! $renamed) {
                return $this->Error([], 'Favorite list not found or unauthorized.', 404);
            }

            return $this->Success([], 'Favorite list renamed successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/favorites/lists/delete
     * حذف ليستا كاملة مع كل عناصرها (cascade)
     */
    public function deleteList(FormRequestFavorite $request): JsonResponse
    {
        try {
            $deleted = $this->_favoriteService->deleteList(
                $request->validated()['list_id'],
                $request->user()->id
            );

            if (! $deleted) {
                return $this->Error([], 'Favorite list not found or unauthorized.', 404);
            }

            return $this->Success([], 'Favorite list deleted successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  TOGGLE  ♥  (إضافة / حذف بضغطة واحدة)
    // ─────────────────────────────────────────────────────────

    /**
     * POST /api/favorites/toggle
     *
     * Body: { "vehicle_id": 5, "favorite_list_id": 1 }
     *
     * Response:
     * {
     *   "data": {
     *     "added":        true,       ← ما تم في هذه الضغطة
     *     "is_favorited": true,       ← لون القلب في الفرونت (أحمر/فارغ)
     *     "list_ids":     [1, 3]      ← الليستات التي تحتوي السيارة الآن
     *   }
     * }
     */
    public function toggle(FormRequestFavorite $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->_favoriteService->toggle(
                $request->user()->id,
                $data['vehicle_id'],
                $data['favorite_list_id']
            );

            $message = $result['added']
                ? 'Vehicle added to favorites.'
                : 'Vehicle removed from favorites.';

            return $this->Success($result, $message);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  MOVE  (نقل عنصر من ليستا إلى أخرى)
    // ─────────────────────────────────────────────────────────

    /**
     * POST /api/favorites/move
     *
     * Body: { "vehicle_id": 5, "from_list_id": 1, "to_list_id": 2 }
     */
    public function move(FormRequestFavorite $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->_favoriteService->move(
                $request->user()->id,
                $data['vehicle_id'],
                $data['from_list_id'],
                $data['to_list_id']
            );

            return $this->Success($result, 'Vehicle moved to new list successfully.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  HEART STATUS  (حالة القلب للفرونت)
    // ─────────────────────────────────────────────────────────

    /**
     * GET /api/favorites/heart?vehicle_id=5
     *
     * Response:
     * {
     *   "data": {
     *     "is_favorited": true,    ← أحمر أو فارغ
     *     "list_ids":     [1, 2]   ← الليستات التي تحتويها
     *   }
     * }
     */
    public function heartStatus(FormRequestFavorite $request): JsonResponse
    {
        try {
            $result = $this->_favoriteService->getHeartStatus(
                $request->user()->id,
                $request->validated()['vehicle_id']
            );

            return $this->Success($result, 'Heart status retrieved.');
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage(), 500);
        }
    }
}
