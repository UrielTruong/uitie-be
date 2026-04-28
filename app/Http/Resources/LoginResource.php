<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class LoginResource extends JsonResource
{
    private string $token;

    public function __construct($resource, string $token)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id'    => $this->id,
                'name'  => $this->name,
                'email' => $this->email,
            ],
            'token' => $this->token,
        ];
    }

    /**
     * Add top-level response envelope.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status'  => true,
            'message' => 'Login successful',
        ];
    }
}
