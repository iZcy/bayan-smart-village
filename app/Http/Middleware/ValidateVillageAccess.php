<?php

// app/Http/Middleware/ValidateVillageAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateVillageAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $village = $request->attributes->get('village');

        if (!$village || !$village->is_active) {
            return response()->json([
                'error' => 'Village is not accessible'
            ], 403);
        }

        // Check if village has any restrictions
        $settings = $village->settings ?? [];

        if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === true) {
            return response()->json([
                'error' => 'Village is under maintenance',
                'message' => $settings['maintenance_message'] ?? 'This village is temporarily unavailable.'
            ], 503);
        }

        return $next($request);
    }
}
