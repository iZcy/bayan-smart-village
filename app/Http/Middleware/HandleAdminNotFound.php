<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleAdminNotFound
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if this is a 404 response in admin area
        if ($response->getStatusCode() === 404 && 
            (str_contains($request->getPathInfo(), '/admin') || 
             str_contains($request->url(), '/admin'))) {
            
            // Don't interfere with JSON/API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Admin page not found',
                    'message' => 'The requested admin page could not be found.'
                ], 404);
            }

            // Return custom admin 404 page
            return response()->view('errors.admin-404', [], 404);
        }

        return $response;
    }
}