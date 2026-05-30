<?php

namespace App\Http\Resources;

use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $svc = app(AttachmentService::class);

        return [
            'id'          => $this->id,
            'content'     => $this->content,
            'sender'      => [
                'id'        => $this->sender->id,
                'full_name' => $this->sender->full_name,
            ],
            'attachments' => $this->whenLoaded('attachments', function () use ($svc) {
                return $this->attachments->map(fn($a) => [
                    'id'        => $a->id,
                    'file_name' => $a->file_name,
                    'file_type' => $a->file_type,
                    'view_url'  => $svc->getViewUrl($a->file_url),
                ]);
            }),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
