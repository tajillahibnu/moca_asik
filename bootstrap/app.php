<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum middleware
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Custom middleware
        $middleware->alias([
            'auth.sanctum' => \App\Http\Middleware\AuthenticateWithSanctum::class,
            'permissions.all' => \App\Http\Middleware\EnsureAllPermissions::class, 
            'permissions.flex' => \App\Http\Middleware\EnsureFlexiblePermissions::class, 
            'ability' => \App\Http\Middleware\EnsureTokenAbilities::class,
        ]);

        // Sanctum config
        $middleware->validateCsrfTokens(except: [
            'sanctum/*',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
