<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function __construct(private JwtService $jwtService) {}

    public function handle(Request $request, Closure $next): Response
    {
        # logger
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not provided',
                'data'    => null,
            ], 401);
        }

        try {
            $payload = $this->jwtService->decode($token);

            // Attach user id to request for use in controllers
            $request->merge(['user_id' => $payload->sub]);
            $request->merge(['user_role' => $payload->role]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token expired',
                'data'    => null,
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token invalid',
                'data'    => null,
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
                'data'    => null,
            ], 401);
        }

        return $next($request);
    }
}
