<?php

namespace App\Http\Resources\Complaint;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FlaggedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'] ?? null;

        return [
            'user' => [
                'id'        => $user?->id,
                'full_name' => $user?->full_name,
                'email'     => $user?->email,
                'status'    => $user?->status,
                'role'      => $user?->getRoleNames()->first(),
                'avatar'    => $user?->image
                    ? url(Storage::url($user->image->path))
                    : url(Storage::url('users/profile-user.png')),
            ],
            'complaints_count' => $this->resource['complaints_count'] ?? 0,
        ];
    }
}
