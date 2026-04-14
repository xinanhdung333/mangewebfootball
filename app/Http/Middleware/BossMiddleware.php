<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BossMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next)
{
    if (!auth()->check() || auth()->user()->role != 'boss') {
        return redirect()->back()->with('error', 'Bạn không có quyền truy cập trang này!');
    }

    return $next($request);
}
}
