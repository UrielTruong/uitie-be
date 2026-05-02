<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->attributes->get('user_role');

        if (! $userRole || ! in_array($userRole, $roles)) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden. You do not have access to this resource.',
                'data'    => null,
            ], 403);
        }

        return $next($request);
    }
}
