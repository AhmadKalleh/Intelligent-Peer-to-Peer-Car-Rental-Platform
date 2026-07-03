<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'full_name'  => $this->full_name,
            'email'      => $this->email,
            'status'     => $this->status,
            'role'       => $this->getRoleNames()->first(),
            'avatar'     => $this->image
                ? url(Storage::url($this->image->path))
                : url(Storage::url('users/profile-user.png')),
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
