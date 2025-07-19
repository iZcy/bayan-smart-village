<?php

namespace App\Http\Middleware;

use App\Models\Village;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveVillageSubdomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        // Check if this is a village subdomain or custom domain
        $village = null;

        if (str_ends_with($host, '.' . $baseDomain)) {
            // Extract village slug from subdomain
            $villageSlug = str_replace('.' . $baseDomain, '', $host);

            // Cache the village lookup for performance
            $village = Cache::remember("village:subdomain:{$villageSlug}", 3600, function () use ($villageSlug) {
                return Village::where('slug', $villageSlug)
                    ->where('is_active', true)
                    ->first();
            });
        } else {
            // Check if this is a custom domain
            $village = Cache::remember("village:domain:{$host}", 3600, function () use ($host) {
                return Village::where('domain', $host)
                    ->where('is_active', true)
                    ->first();
            });
        }

        if (!$village) {
            abort(404, 'Village not found');
        }

        // Add village to request attributes
        $request->attributes->set('village', $village);

        // Set village in view for easy access
        view()->share('village', $village);

        return $next($request);
    }
}
