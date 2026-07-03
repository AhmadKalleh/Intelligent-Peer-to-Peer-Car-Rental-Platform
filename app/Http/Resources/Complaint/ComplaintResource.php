<?php

namespace App\Http\Resources\Complaint;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,

            'complainant'   => $this->when($this->complainant, [
                'id'        => $this->complainant?->id,
                'full_name' => $this->complainant?->full_name,
                'role'      => $this->complainant?->getRoleNames()->first(),
                'avatar'    => $this->complainant?->image
                    ? url(Storage::url($this->complainant->image->path))
                    : url(Storage::url('users/profile-user.png')),
            ]),

            'reported_user' => $this->when($this->reportedUser, [
                'id'        => $this->reportedUser?->id,
                'full_name' => $this->reportedUser?->full_name,
                'role'      => $this->reportedUser?->getRoleNames()->first(),
                'avatar'    => $this->reportedUser?->image
                    ? url(Storage::url($this->reportedUser->image->path))
                    : url(Storage::url('users/profile-user.png')),
            ]),

            'reason' => [
                'key'     => $this->reason_key,
                'subject' => $this->reason_subject,
                'text'    => $this->reason_text,
            ],

            'details' => $this->details,

            // يظهر الرد فقط إذا كانت الشكوى مردود عليها
            'reply' => $this->when($this->status === 'answered', [
                'admin_reply' => $this->admin_reply,
                'replied_by'  => $this->repliedByAdmin?->full_name,
                'replied_at'  => $this->replied_at?->toDateTimeString(),
            ]),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
