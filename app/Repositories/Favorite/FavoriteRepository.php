<?php

namespace App\Repositories\Favorite;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Repositories\Favorite\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    // ─── Favorite Lists ───────────────────────────────────────

    public function createList(int $userId, string $name): FavoriteList
    {
        return FavoriteList::create([
            'user_id' => $userId,
            'name'    => $name,
        ]);
    }

    public function getAllLists(int $userId): Collection
    {
        return FavoriteList::query()
            ->where('user_id', $userId)
            ->withCount('favorites')
            ->with([
                'vehicles' => function ($q) {
                    $q->with('primaryImage')->limit(1);
                }
            ])
            ->latest()
            ->get();
    }

    public function getListWithVehicles(int $listId, int $userId): ?FavoriteList
    {
        return FavoriteList::query()
            ->where('id',      $listId)   // ✅ bug fixed (was $userId)
            ->where('user_id', $userId)
            ->with([
                'vehicles' => function ($q) {
                    $q->select([
                        'vehicles.id',
                        'vehicles.make',
                        'vehicles.model',
                        'vehicles.year',
                        'vehicles.city',
                        'vehicles.base_price_per_day',
                        'vehicles.rating_avg',
                        'vehicles.total_bookings',
                        'vehicles.listing_status',
                        'vehicles.admin_review_status',
                        'vehicles.snoozed_until',
                    ])
                    ->with('primaryImage')
                    ->withCurrentPrice();
                }
            ])
            ->first();
    }

    public function deleteList(int $listId, int $userId): bool
    {
        return (bool) FavoriteList::where('id',      $listId)
                                  ->where('user_id', $userId)
                                  ->delete();
    }

    public function renameList(int $listId, int $userId, string $name): bool
    {
        return (bool) FavoriteList::where('id',      $listId)
                                  ->where('user_id', $userId)
                                  ->update(['name' => $name]);
    }

    // ─── Favorites ────────────────────────────────────────────

    public function toggle(int $userId, int $vehicleId, int $listId): array
    {
        $existing = Favorite::where('user_id',          $userId)
                            ->where('vehicle_id',       $vehicleId)
                            ->where('favorite_list_id', $listId)
                            ->first();

        if ($existing) {
            $existing->delete();
            return ['added' => false, 'favorite' => null];
        }

        $favorite = Favorite::create([
            'user_id'          => $userId,
            'vehicle_id'       => $vehicleId,
            'favorite_list_id' => $listId,
        ]);

        return ['added' => true, 'favorite' => $favorite];
    }

    public function move(int $userId, int $vehicleId, int $fromListId, int $toListId): Favorite
    {
        Favorite::where('user_id',          $userId)
                ->where('vehicle_id',       $vehicleId)
                ->where('favorite_list_id', $fromListId)
                ->delete();

        return Favorite::firstOrCreate([
            'user_id'          => $userId,
            'vehicle_id'       => $vehicleId,
            'favorite_list_id' => $toListId,
        ]);
    }

    public function isFavorited(int $userId, int $vehicleId): bool
    {
        return Favorite::where('user_id',    $userId)
                       ->where('vehicle_id', $vehicleId)
                       ->exists();
    }

    public function getFavoritedListIds(int $userId, int $vehicleId): array
    {
        return Favorite::where('user_id',    $userId)
                       ->where('vehicle_id', $vehicleId)
                       ->pluck('favorite_list_id')
                       ->toArray();
    }
}
