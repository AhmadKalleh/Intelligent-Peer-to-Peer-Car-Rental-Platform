<?php
// app/Services/Feature/FeatureService.php

namespace App\Services\Feature;

use App\Repositories\Feature\Interfaces\FeatureRepositoryInterface;

class FeatureService
{
    public function __construct(
        protected FeatureRepositoryInterface $_featureRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(): array
    {
        $features = $this->_featureRepository->index();

        return [
            'data'    => $features,
            'message' => 'Features retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // STORE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function store(array $data): array
    {
        $feature = $this->_featureRepository->store($data);

        return [
            'data'    => $feature,
            'message' => 'Feature created successfully.',
            'code'    => 201,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // UPDATE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function update(int $id, array $data): array
    {
        $feature = $this->_featureRepository->update($id, $data);

        return [
            'data'    => $feature,
            'message' => 'Feature updated successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DESTROY
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function destroy(int $id): array
    {
        $this->_featureRepository->destroy($id);

        return [
            'data'    => [],
            'message' => 'Feature deleted successfully.',
            'code'    => 200,
        ];
    }
}
