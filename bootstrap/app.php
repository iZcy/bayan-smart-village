<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Middleware as InertiaMiddleware;
use App\Http\Middleware\ResolveVillageSubdomain;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve.village' => ResolveVillageSubdomain::class,
        ]);

        // Add this line to register Inertia middleware globally for 'web'
        $middleware->appendToGroup('web', InertiaMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
