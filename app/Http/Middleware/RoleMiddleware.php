<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Vérifie si l'utilisateur est authentifié et a le rôle requis
        if (!auth()->check() || !$request->user()->hasRole($role)) {
            return response()->json(['erreur' => 'Accès non autorisé'], 403);
        }
        return $next($request);
    }
}
