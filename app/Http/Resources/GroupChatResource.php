<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $myMember   = $this->members->first();
        $lastMessage = $this->messages->first();

        return [
            'id'           => $this->id,
            'group_name'   => $this->group_name,
            'created_by'   => $this->created_by,
            'member_count' => $this->members()->count(),
            'my_status'    => $myMember?->status,
            'last_message' => $lastMessage ? [
                'content'    => $lastMessage->content,
                'created_at' => $lastMessage->created_at?->toISOString(),
                'sender'     => [
                    'id'        => $lastMessage->sender?->id,
                    'full_name' => $lastMessage->sender?->full_name,
                ],
            ] : null,
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
