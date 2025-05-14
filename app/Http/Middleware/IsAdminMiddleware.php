<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()?->isAdmin()) {
            return response()->json(['message' => 'Accès non autorisé. Réservé aux administrateurs.'], 403);
        }
        return $next($request);
    }
}
