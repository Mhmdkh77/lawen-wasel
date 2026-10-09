<?php

use App\Http\Middleware\EnsureDriverIsVerified;
use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsDriver;
use App\Http\Middleware\EnsureUserIsPassenger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin-active' => EnsureAdminIsActive::class,
            'super-admin' => EnsureSuperAdmin::class,
            'driver' =>   EnsureUserIsDriver::class,
            'driver-verified' =>  EnsureDriverIsVerified::class,
            'passenger' => EnsureUserIsPassenger::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
