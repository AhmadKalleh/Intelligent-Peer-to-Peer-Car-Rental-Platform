<?php
// app/Repositories/Search/SearchAdminRepository.php

namespace App\Repositories\Search;

use App\Models\User;
use App\Repositories\Search\Interfaces\SearchAdminRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;


class SearchAdminRepository implements SearchAdminRepositoryInterface
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SEARCH USERS  (Admin)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function searchUsers(string $keyword, int $perPage): CursorPaginator
    {
        return User::query()
            ->with(['host', 'image'])
            ->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($perPage);
    }
}
