<?php
// app/Http/Controllers/Api/Feature/FeatureController.php

namespace App\Http\Controllers\Api\Feature;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureRequests\FormRequestFeature;
use App\Http\Resources\FeatureResource;
use App\Services\Feature\FeatureService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class FeatureController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected FeatureService $_featureService
    ) {}

    // ─── GET /api/features ───────────────────────────────────────
    public function index(): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_featureService->index();
            return $this->Success(
                FeatureResource::collection($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── POST /api/admin/features ────────────────────────────────
    public function store(FormRequestFeature $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_featureService->store($request->validated());
            return $this->Success(
                new FeatureResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── PUT /api/admin/features/{id} ────────────────────────────
    public function update(FormRequestFeature $request, int $id): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_featureService->update($id, $request->validated());
            return $this->Success(
                new FeatureResource($result['data']),
                $result['message'],
                $result['code']
            );
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }

    // ─── DELETE /api/admin/features/{id} ─────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_featureService->destroy($id);
            return $this->Success($result['data'], $result['message'], $result['code']);
        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
