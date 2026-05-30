<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user'         => [
                'id'        => $this['user']->id,
                'full_name' => $this['user']->full_name,
            ],
            'last_message' => $this['last_message'] ? [
                'content'    => $this['last_message']->content,
                'created_at' => $this['last_message']->created_at?->toISOString(),
            ] : null,
        ];
    }
}
