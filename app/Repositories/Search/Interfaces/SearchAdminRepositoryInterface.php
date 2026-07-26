<?php
// app/Repositories/Search/Interfaces/SearchAdminRepositoryInterface.php

namespace App\Repositories\Search\Interfaces;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface SearchAdminRepositoryInterface
{
    public function searchUsers(string $keyword, int $perPage): CursorPaginator;
}
