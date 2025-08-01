<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Middleware as InertiaMiddleware;
use App\Http\Middleware\ResolveVillageSubdomain;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\ApiRateLimit;
use App\Http\Middleware\ValidateVillageAccess;
use App\Http\Middleware\CheckDomainAccess;
use App\Http\Middleware\HandleAdminNotFound;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'resolve.village' => ResolveVillageSubdomain::class,
            'cors' => CorsMiddleware::class,
            'api.rate.limit' => ApiRateLimit::class,
            'village.access' => ValidateVillageAccess::class,
            'domain.access' => CheckDomainAccess::class,
            'admin.404' => HandleAdminNotFound::class,
        ]);

        // Add Inertia middleware to web group
        $middleware->appendToGroup('web', InertiaMiddleware::class);

        // Add CORS middleware to API group
        $middleware->appendToGroup('api', CorsMiddleware::class);

        // Add rate limiting to API group
        $middleware->appendToGroup('api', ApiRateLimit::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle village not found exceptions (HTTP 404 exceptions)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            if (request()->expectsJson()) {
                // Check if this is an admin-related request
                if (str_contains(request()->getPathInfo(), '/admin')) {
                    return response()->json([
                        'error' => 'Admin page not found',
                        'message' => 'The requested admin page could not be found.'
                    ], 404);
                }
                
                return response()->json([
                    'error' => 'Village not found',
                    'message' => 'The requested village subdomain could not be found.'
                ], 404);
            }

            // Check if this is an admin-related 404
            if (str_contains(request()->getPathInfo(), '/admin') || 
                str_contains(request()->url(), '/admin')) {
                return response()->view('errors.admin-404', [], 404);
            }

            // Check if this is a village subdomain issue
            $host = request()->getHost();
            $baseDomain = config('app.domain', 'kecamatanbayan.id');
            
            if (str_ends_with($host, '.' . $baseDomain) || 
                ($host !== $baseDomain && !str_contains($host, 'www.'))) {
                return response()->view('errors.404', ['message' => 'Village not found'], 404);
            }

            // General 404 for main domain
            return response()->view('errors.404', [], 404);
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Resource not found',
                    'message' => 'The requested resource could not be found.'
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle validation exceptions for API
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        // Handle rate limit exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Rate limit exceeded',
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
                ], 429);
            }
        });
    })->create();
