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
     * List of reserved subdomains that should not be treated as villages
     */
    private const RESERVED_SUBDOMAINS = ['www', 'mail', 'ftp', 'admin', 'api', 'cdn', 'static'];

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

            // Skip village lookup for reserved subdomains
            if (!in_array($villageSlug, self::RESERVED_SUBDOMAINS)) {
                // Cache the village lookup for performance
                $village = Cache::remember("village:subdomain:{$villageSlug}", 3600, function () use ($villageSlug) {
                    return Village::where('slug', $villageSlug)
                        ->where('is_active', true)
                        ->first();
                });
            }
        } else {
            // Check if this is a custom domain
            $village = Cache::remember("village:domain:{$host}", 3600, function () use ($host) {
                return Village::where('domain', $host)
                    ->where('is_active', true)
                    ->first();
            });
        }

        if (!$village) {
            // Allow main domain to pass through without village
            if ($host === $baseDomain || $host === 'localhost') {
                // No village for main domain - this is expected
                return $next($request);
            }
            
            // Allow reserved subdomains to pass through without village
            if (str_ends_with($host, '.' . $baseDomain)) {
                $villageSlug = str_replace('.' . $baseDomain, '', $host);
                
                if (in_array($villageSlug, self::RESERVED_SUBDOMAINS)) {
                    // Reserved subdomain - treat like main domain
                    return $next($request);
                }
            }
            
            // Log the attempt for debugging
            \Illuminate\Support\Facades\Log::info('Village subdomain not found', [
                'host' => $host,
                'extracted_slug' => str_ends_with($host, '.' . $baseDomain) ? str_replace('.' . $baseDomain, '', $host) : null,
                'is_custom_domain' => !str_ends_with($host, '.' . $baseDomain),
            ]);
            
            abort(404, 'Village not found');
        }

        // Add village to request attributes
        $request->attributes->set('village', $village);

        // Set village in view for easy access
        view()->share('village', $village);

        return $next($request);
    }
}
