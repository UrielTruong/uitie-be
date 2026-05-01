<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'full_name'     => $this->full_name,
            'email'         => $this->email,
            'mssv'          => $this->mssv,
            'phone_number'  => $this->phone_number,
            'faculty'       => $this->faculty,
            'class_name'    => $this->class_name,
            'academic_year' => $this->academic_year,
            'role'          => $this->role,
            'status'        => $this->status,
            'created_at'    => $this->created_at?->toISOString(),

        ];
    }
}
