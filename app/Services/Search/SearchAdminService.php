<?php
// app/Services/Search/SearchAdminService.php

namespace App\Services\Search;

use App\Repositories\Search\Interfaces\SearchAdminRepositoryInterface;

class SearchAdminService
{
    public function __construct(
        protected SearchAdminRepositoryInterface $_searchAdminRepository
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH USERS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchUsers(array $filters): array
    {
        $result = $this->_searchAdminRepository->searchUsers(
            keyword: $filters['keyword'],
            perPage: $filters['per_page'] ?? 15,
        );

        return [
            'data'    => $result,
            'message' => 'Users search results retrieved successfully.',
            'code'    => 200,
        ];
    }
}
