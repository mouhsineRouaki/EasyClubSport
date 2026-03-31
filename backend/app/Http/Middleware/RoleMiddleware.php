<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();

        if (! $utilisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifie.',
                'data' => null,
            ], 401);
        }

        if (! in_array($utilisateur->role, $roles, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Acces refuse pour ce role.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
