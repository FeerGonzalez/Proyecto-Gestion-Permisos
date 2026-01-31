<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado',
            ], 401);
        }

        if (! $user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'No tiene permisos para realizar esta acción',
            ], 403);
        }

        return $next($request);
    }
}
