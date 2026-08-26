<?php
// app/Http/Controllers/Api/Search/HostSearchController.php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequests\FormRequestHostSearch;
use App\Http\Resources\Vehicle\VehicleHostListResource;
use App\Services\Search\SearchQueryService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class HostSearchController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected SearchQueryService $_searchQueryService
    ) {}

    // ─── GET /api/host/search ─────────────────────────────────
    public function search(FormRequestHostSearch $request): JsonResponse
    {
        $data = [];
        try {
            $hostId = auth()->user()->host->id;
            $result = $this->_searchQueryService->searchHost($hostId, $request->validated());

            return $this->Success([
                'vehicles'    => VehicleHostListResource::collection($result['data']),
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
