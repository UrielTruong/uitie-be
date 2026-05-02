<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Post */
class AdminPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'content'       => $this->content,
            'visibility'    => $this->visibility,
            'status'        => $this->status,
            'reject_reason' => $this->reject_reason, // admin cần biết lý do từ chối
            'is_edited'     => $this->is_edited,
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
            'author'        => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'email'     => $this->user->email,
                'role'      => $this->user->role,
            ],
            'category'      => $this->when($this->category, [
                'id'   => $this->category?->id,
                'name' => $this->category?->category_name,
            ]),
        ];
    }
}
