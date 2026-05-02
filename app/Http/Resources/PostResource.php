<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'content'     => $this->content,
            'visibility'  => $this->visibility,
            'status'      => $this->status,
            'is_edited'   => $this->is_edited,
            'updated_at'  => $this->updated_at,
            'created_at'  => $this->created_at,
            'author'      => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'email'     => $this->user->email,
            ],
            'category'    => $this->when($this->category, [
                'id'   => $this->category?->id,
                'category_name' => $this->category?->category_name,
            ]),
        ];
    }
}
