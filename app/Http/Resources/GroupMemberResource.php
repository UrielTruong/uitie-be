<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'user'      => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
            ],
            'status'    => $this->status,
            'joined_at' => $this->joined_at?->toISOString(),
        ];
    }
}
