<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

    $middleware->validateCsrfTokens(except: [
        'momo/ipn',
        'booking/momo/ipn',
        'api/mbbank/*',
    ]);

    // thêm dòng này
    $middleware->alias([
        'boss' => \App\Http\Middleware\BossMiddleware::class,
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
    ]);

})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
