<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictShipperAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'shipper') {
            $routeName = (string) optional($request->route())->getName();

            if (! str_starts_with($routeName, 'shipper.')
                && ! in_array($routeName, ['logout', 'login', 'login.post'], true)) {
                return redirect()->route('shipper.dashboard');
            }
        }

        return $next($request);
    }
}
