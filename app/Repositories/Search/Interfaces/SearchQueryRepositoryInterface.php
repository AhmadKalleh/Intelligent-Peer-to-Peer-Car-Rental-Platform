<?php
// app/Repositories/Search/Interfaces/SearchQueryRepositoryInterface.php

namespace App\Repositories\Search\Interfaces;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface SearchQueryRepositoryInterface
{
    public function searchGuest(array $filters): CursorPaginator;
    public function filterGuest(array $filters): CursorPaginator;
    public function searchHost(int $hostId, string $keyword, int $perPage, ?string $cursor): CursorPaginator;
}
