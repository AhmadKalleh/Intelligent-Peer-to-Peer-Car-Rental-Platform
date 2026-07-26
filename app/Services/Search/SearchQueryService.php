<?php
// app/Services/Search/SearchQueryService.php

namespace App\Services\Search;

use App\Repositories\Search\Interfaces\SearchQueryRepositoryInterface;

class SearchQueryService
{
    public function __construct(
        protected SearchQueryRepositoryInterface $_searchQueryRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH GUEST
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchGuest(array $filters): array
    {
        $result = $this->_searchQueryRepository->searchGuest($filters);

        return [
            'data'    => $result,
            'message' => 'Search results retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // FILTER GUEST
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function filterGuest(array $filters): array
    {
        $result = $this->_searchQueryRepository->filterGuest($filters);

        return [
            'data'    => $result,
            'message' => 'Filtered results retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH HOST
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchHost(int $hostId, array $filters): array
    {
        $result = $this->_searchQueryRepository->searchHost(
            hostId : $hostId,
            keyword: $filters['keyword'],
            perPage: $filters['per_page'] ?? 2,
            cursor : $filters['cursor']   ?? null,
        );

        return [
            'data'    => $result,
            'message' => 'Host search results retrieved successfully.',
            'code'    => 200,
        ];
    }
}
