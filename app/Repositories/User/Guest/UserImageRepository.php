<?php

namespace App\Repositories\User\Guest;

use App\Models\Image;
use App\Models\User;
use App\Repositories\User\Interfaces\UserImageRepositoryInterface;
use App\Traits\Upload\UplodeImageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserImageRepository implements UserImageRepositoryInterface
{
    use UplodeImageHelper;

    // ─── Update Profile Image (Admin / Guest / Host) ──────────────────────────

    public function updateProfileImage(int $userId, $file): array
    {
        return DB::transaction(function () use ($userId, $file) {

            $user = User::findOrFail($userId);

            // Delete old image from storage and DB if exists
            $existingImage = $user->image;

            if ($existingImage) {
                Storage::disk('public')->delete($existingImage->path);
                $existingImage->delete();
            }

            // Upload new image
            $uploaded = $this->uploadImage($file, 'users/avatars');

            // Save to images table via morph
            $image = Image::create([
                'imageable_type' => User::class,
                'imageable_id'   => $user->id,
                'path'           => $uploaded['path'],
                'hash'           => $uploaded['hash'],
                'type'           => 'profile_image',
                'is_primary'     => true,
                'sort_order'     => 0,
            ]);

            return [
                'status' => 'updated',
                'image'  => $image,
                'url'    => url(Storage::url($uploaded['path'])),
            ];
        });
    }
}