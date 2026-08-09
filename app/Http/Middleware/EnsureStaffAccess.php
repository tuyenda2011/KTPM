<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role !== 'staff') {
            abort(403);
        }

        $targetUserId = (string) $request->route('userId');
        if ($targetUserId === '' || $targetUserId !== (string) $user->user_id) {
            abort(403);
        }

        return $next($request);
    }
}
