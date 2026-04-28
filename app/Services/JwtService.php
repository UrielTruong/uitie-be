<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtService
{
    private string $secret;
    private string $algorithm;
    private int $expiresIn;

    public function __construct()
    {
        $this->secret    = config('jwt.secret');
        $this->algorithm = config('jwt.algorithm');
        $this->expiresIn = config('jwt.expires_in');
    }

    public function encode(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),   // issuer
            'sub' => $user->id,           // subject
            'iat' => time(),              // issued at
            'role' => $user->role,        // role
            'exp' => time() + $this->expiresIn,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, $this->algorithm));
    }
}
