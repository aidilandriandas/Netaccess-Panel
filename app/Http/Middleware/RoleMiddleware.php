<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($role === 'owner' && !$user->isOwner()) {
            abort(403, 'Unauthorized. Owner access required.');
        }

        if ($role === 'admin' && !$user->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        if ($role === 'client' && !$user->isClient()) {
            abort(403, 'Unauthorized. Client access required.');
        }

        return $next($request);
    }
}
