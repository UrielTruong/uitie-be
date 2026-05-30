<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $request->attributes->set('user_id', $payload->sub);
            $request->attributes->set('user_role', $payload->role);

            // Allow Auth::user() to work for this request (needed for Reverb channel auth).
            // loginUsingOnce requires SessionGuard (web), which is not available on stateless API routes,
            // so we fall back to setUser() which works on any guard without session.
            if ($user = User::find($payload->sub)) {
                try {
                    Auth::guard('web')->loginUsingOnce($user);
                } catch (\BadMethodCallException) {
                    Auth::setUser($user);
                }
            }
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
