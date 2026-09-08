<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie role/permission middleware aliases
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active'             => \App\Http\Middleware\EnsureUserIsActive::class,
            'force.password'     => \App\Http\Middleware\RequirePasswordChange::class,
        ]);

        // NOTE: EnsureUserIsActive is NOT added globally here.
        // It is applied only inside authenticated route groups in web.php
        // to avoid running before the auth middleware.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
