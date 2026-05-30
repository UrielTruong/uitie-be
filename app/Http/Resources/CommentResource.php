<?php

namespace App\Http\Resources;

use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_comment_id' => $this->parent_comment_id,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
            ],
            'attachments'       => $this->whenLoaded('attachments', function () {
                $svc = app(AttachmentService::class);
                return $this->attachments->map(fn($att) => [
                    'id'         => $att->id,
                    'file_type'  => $att->file_type,
                    'file_name'  => $att->file_name,
                    'file_url'   => $att->file_url,
                    'view_url'   => $svc->getViewUrl($att->file_url),
                    'created_at' => $att->created_at,
                ]);
            }),
        ];
    }
}