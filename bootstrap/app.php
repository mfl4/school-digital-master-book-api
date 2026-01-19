<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Bootstrap aplikasi Laravel
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust proxies untuk Railway/load balancer
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Registrasi middleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'trusted' => \App\Http\Middleware\TrustProxies::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi exception handling
    })
    ->create();
