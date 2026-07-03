<?php

namespace App\Repositories\User\Interfaces;

interface UserImageRepositoryInterface
{
    public function updateProfileImage(int $userId, $file): array;
}
