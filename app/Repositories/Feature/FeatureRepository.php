<?php
// app/Repositories/FeatureRepository.php

namespace App\Repositories\Feature;

use App\Models\Feature;
use App\Repositories\Feature\Interfaces\FeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class FeatureRepository implements FeatureRepositoryInterface
{
    private string $cacheKey = 'features:all';

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX  (Redis Cache)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(): Collection
    {
        $cached = Redis::get($this->cacheKey);

        if ($cached) {
            // نرجع Collection من البيانات المخزنة
            return Feature::hydrate(json_decode($cached, true));
        }

        $features = Feature::query()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get();

        Redis::setex($this->cacheKey, 3600, $features->toJson());

        return $features;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // STORE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function store(array $data): Feature
    {
        $feature = Feature::create([
            'name' => $data['name'],
        ]);

        Redis::del($this->cacheKey);

        return $feature;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function update(int $id, array $data): Feature
    {
        $feature = Feature::findOrFail($id);

        $feature->update([
            'name' => $data['name'],
        ]);

        Redis::del($this->cacheKey);

        return $feature->fresh();
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DESTROY
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function destroy(int $id): bool
    {
        $feature = Feature::findOrFail($id);
        $deleted = $feature->delete();

        Redis::del($this->cacheKey);

        return $deleted;
    }
}
