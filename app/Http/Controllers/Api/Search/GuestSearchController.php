<?php
// app/Http/Controllers/Api/Search/GuestSearchController.php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequests\FormRequestGuestSearch;
use App\Http\Resources\Vehicle\VehicleGuestListResource;
use App\Services\Search\SearchQueryService;
use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Throwable;

class GuestSearchController extends Controller
{
    use ResponseHelper;

    public function __construct(
        protected SearchQueryService $_searchQueryService
    ) {}

    public function search(FormRequestGuestSearch $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_searchQueryService->searchGuest($request->validated());

            return $this->Success([
                'vehicles'    => VehicleGuestListResource::collection($result['data']),
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

    public function filter(FormRequestGuestSearch $request): JsonResponse
    {
        $data = [];
        try {
            $result = $this->_searchQueryService->filterGuest($request->validated());

            return $this->Success([
                'vehicles'    => VehicleGuestListResource::collection($result['data']),
                'next_cursor' => $result['data']->nextCursor()?->encode(),
                'prev_cursor' => $result['data']->previousCursor()?->encode(),
                'per_page'    => $result['data']->perPage(),
                'has_more'    => $result['data']->hasMorePages(),
            ], $result['message'], $result['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage(), 500);
        }
    }
}
