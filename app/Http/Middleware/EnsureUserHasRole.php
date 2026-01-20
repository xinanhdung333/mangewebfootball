<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
        if (! $user || $user->role !== $role) {
            abort(403, 'Unauthorized.');
        }
        return $next($request);
    }
}
