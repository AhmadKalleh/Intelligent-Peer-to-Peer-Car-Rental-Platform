<?php

namespace App\Services\User\Guest;

use App\Repositories\User\Interfaces\UserImageRepositoryInterface;

class UserImageService
{
    public function __construct(
        protected UserImageRepositoryInterface $_userImageRepository
    ) {}

    // ─── Update Profile Image ─────────────────────────────────────────────────

    public function updateProfileImage(int $userId, $file): array
    {
        $result = $this->_userImageRepository->updateProfileImage($userId, $file);

        return [
            'data'    => ['url' => $result['url']],
            'message' => 'Profile image updated successfully.',
            'code'    => 200,
        ];
    }
}
