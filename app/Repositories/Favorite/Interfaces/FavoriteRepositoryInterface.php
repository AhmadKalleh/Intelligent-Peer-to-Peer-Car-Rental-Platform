<?php

namespace App\Repositories\Favorite\Interfaces;

use App\Models\Favorite;
use App\Models\FavoriteList;
use Illuminate\Database\Eloquent\Collection;

interface FavoriteRepositoryInterface
{
    // ─── Lists ────────────────────────────────────────────────
    public function createList(int $userId, string $name): FavoriteList;
    public function getAllLists(int $userId): Collection;
    public function getListWithVehicles(int $listId, int $userId): ?FavoriteList;
    public function deleteList(int $listId, int $userId): bool;
    public function renameList(int $listId, int $userId, string $name): bool;

    // ─── Toggle / Move ───────────────────────────────────────
    public function toggle(int $userId, int $vehicleId, int $listId): array;
    public function move(int $userId, int $vehicleId, int $fromListId, int $toListId): Favorite;

    // ─── Status helpers ──────────────────────────────────────
    public function isFavorited(int $userId, int $vehicleId): bool;
    public function getFavoritedListIds(int $userId, int $vehicleId): array;
}
