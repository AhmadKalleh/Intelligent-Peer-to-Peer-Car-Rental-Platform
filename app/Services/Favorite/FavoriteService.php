<?php

namespace App\Services\Favorite;

use App\Models\Vehicle;
use App\Repositories\Favorite\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class FavoriteService
{
    public function __construct(
        protected FavoriteRepositoryInterface $_favoriteRepository
    ) {}

    // ─────────────────────────────────────────────────────────
    //  LISTS
    // ─────────────────────────────────────────────────────────

    public function createList(int $userId, string $name): array
    {
        $list = $this->_favoriteRepository->createList($userId, $name);

        return [];
    }

    public function getAllLists(int $userId): array
    {
        $lists = $this->_favoriteRepository->getAllLists($userId);

        return $lists->map(function ($list) {
            $thumbnail = null;

            if ($list->vehicles->isNotEmpty()) {
                $img       = $list->vehicles->first()?->primaryImage;
                $thumbnail = $img ? url(Storage::url($img->path)) : null;
            }

            return [
                'id'              => $list->id,
                'name'            => $list->name,
                'favorites_count' => $list->favorites_count,
                'thumbnail'       => $thumbnail,
                'created_at'      => $list->created_at->format('Y-m-d'),
            ];
        })->values()->toArray();
    }

    public function getListWithVehicles(int $listId, int $userId): array
    {
        $list = $this->_favoriteRepository->getListWithVehicles($listId, $userId);

        if (! $list) {
            return [];
        }

        $vehicles = $list->vehicles->map(
            fn($v) => $this->enrichVehicle($v)
        )->values()->toArray();

        return [
            'id'       => $list->id,
            'name'     => $list->name,
            'vehicles' => $vehicles,
        ];
    }

    public function deleteList(int $listId, int $userId): bool
    {
        return $this->_favoriteRepository->deleteList($listId, $userId);
    }

    public function renameList(int $listId, int $userId, string $name): bool
    {
        return $this->_favoriteRepository->renameList($listId, $userId, $name);
    }

    // ─────────────────────────────────────────────────────────
    //  TOGGLE  ♥
    // ─────────────────────────────────────────────────────────

    /**
     * Toggle المفضلة — يرجع الحالة الجديدة ولون القلب للفرونت
     *
     * Response shape:
     * {
     *   "added":        bool,   ← ما تم في هذه العملية
     *   "is_favorited": bool,   ← الحالة الكلية (لون القلب)
     *   "list_ids":     int[]   ← الليستات التي تحتوي السيارة الآن
     * }
     */
    public function toggle(int $userId, int $vehicleId, int $listId): array
    {
        $result      = $this->_favoriteRepository->toggle($userId, $vehicleId, $listId);
        $isFavorited = $this->_favoriteRepository->isFavorited($userId, $vehicleId);
        $listIds     = $this->_favoriteRepository->getFavoritedListIds($userId, $vehicleId);

        return [
            'added'        => $result['added'],
            'is_favorited' => $isFavorited,
            'list_ids'     => $listIds,
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  MOVE
    // ─────────────────────────────────────────────────────────

    public function move(int $userId, int $vehicleId, int $fromListId, int $toListId): array
    {
        $this->_favoriteRepository->move($userId, $vehicleId, $fromListId, $toListId);

        return [
            'vehicle_id'   => $vehicleId,
            'from_list_id' => $fromListId,
            'to_list_id'   => $toListId,
            'list_ids'     => $this->_favoriteRepository->getFavoritedListIds($userId, $vehicleId),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  HEART STATUS
    // ─────────────────────────────────────────────────────────

    public function getHeartStatus(int $userId, int $vehicleId): array
    {
        return [
            'is_favorited' => $this->_favoriteRepository->isFavorited($userId, $vehicleId),
            'list_ids'     => $this->_favoriteRepository->getFavoritedListIds($userId, $vehicleId),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  VEHICLE STATUS ENRICHMENT
    //  تحديث حالة السيارة داخل المفضلة تلقائياً
    // ─────────────────────────────────────────────────────────

    private function enrichVehicle(Vehicle $vehicle): array
    {
        $img = $vehicle->primaryImage;

        return [
            'id'                 => $vehicle->id,
            'make'               => $vehicle->make,
            'model'              => $vehicle->model,
            'year'               => $vehicle->year,
            'city'               => $vehicle->city,
            'base_price_per_day' => (float) ($vehicle->current_price ?? $vehicle->base_price_per_day),
            'rating_avg'         => (float) ($vehicle->rating_avg   ?? 0.0),
            'total_bookings'     => (int)    $vehicle->total_bookings,
            'primary_image'      => $img ? url(Storage::url($img->path)) : null,
            'vehicle_status'     => $this->resolveVehicleStatus($vehicle),
        ];
    }

    /**
     * قواعد تحديد حالة السيارة داخل المفضلة:
     *
     * suspended   → admin_review_status != approved  (إيقاف إداري أو رفض)
     * deleted     → trashed (soft-deleted)
     * unavailable → listing_status != listed  أو  snoozed_until لم ينته بعد
     * booked      → يوجد حجز confirmed/ongoing يغطي اليوم الحالي
     * available   → كل الشروط السابقة غير منطبقة
     */
    private function resolveVehicleStatus(Vehicle $vehicle): string
    {
        // 1. محذوفة (soft-delete)
        if (isset($vehicle->deleted_at) && $vehicle->deleted_at !== null) {
            return 'deleted';
        }

        // 2. موقوفة إدارياً أو مرفوضة
        if ($vehicle->admin_review_status !== 'approved') {
            return 'suspended';
        }

        // 3. Snoozed مؤقتاً
        if (
            $vehicle->listing_status === 'snoozed' &&
            $vehicle->snoozed_until &&
            now()->isBefore($vehicle->snoozed_until)
        ) {
            return 'unavailable';
        }

        // 4. غير مدرجة (unlisted / draft)
        if ($vehicle->listing_status !== 'listed') {
            return 'unavailable';
        }

        // 5. محجوزة حالياً
        $isBooked = $vehicle->bookings()
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date',   '>=', now()->toDateString())
            ->exists();

        if ($isBooked) {
            return 'booked';
        }

        // 6. متاحة ✅
        return 'available';
    }
}
