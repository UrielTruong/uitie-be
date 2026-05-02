<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class AdminReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reason'           => $this->reason,
            'status'           => $this->status,
            'target_type'      => $this->target_type,
            'reporter'         => $this->whenLoaded('reporter', fn() => [
                'id'        => $this->reporter?->id,
                'full_name' => $this->reporter?->full_name,
                'email'     => $this->reporter?->email,
            ]),
            'reported_user'    => $this->whenLoaded('reportedUser', fn() => $this->reportedUser ? [
                'id'        => $this->reportedUser->id,
                'full_name' => $this->reportedUser->full_name,
                'email'     => $this->reportedUser->email,
            ] : null),
            'reported_post'    => $this->whenLoaded('reportedPost', fn() => $this->reportedPost ? [
                'id'      => $this->reportedPost->id,
                'content' => $this->reportedPost->content,
            ] : null),
            'resolver'         => $this->whenLoaded('resolver', fn() => $this->resolver ? [
                'id'        => $this->resolver->id,
                'full_name' => $this->resolver->full_name,
            ] : null),
            'created_at'    => $this->created_at?->toISOString()
        ];
    }
}
