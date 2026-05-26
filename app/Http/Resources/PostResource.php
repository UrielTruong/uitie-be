<?php

namespace App\Http\Resources;

use App\Services\AttachmentService;
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
            'comments'    => $this->comments_count ?? $this->comments()->count(),
            'author'      => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'email'     => $this->user->email,
            ],
            'category'    => $this->when($this->category, [
                'id'   => $this->category?->id,
                'category_name' => $this->category?->category_name,
            ]),
            'attachments' => $this->whenLoaded(
                'attachments',
                function () {
                    $svc = app(AttachmentService::class);
                    return $this->attachments->map(fn($a) => [
                        'id'        => $a->id,
                        'file_name' => $a->file_name,
                        'file_url'  => $a->file_url,
                        'view_url'  => $svc->getViewUrl($a->file_url),
                        'file_type' => $a->file_type,
                    ]);
                }
            ),
        ];
    }
}
