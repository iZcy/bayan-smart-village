<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Village;
use Illuminate\Support\Facades\Log;

class ResolveVillageSubdomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Add debug logging
        Log::info("ResolveVillageSubdomain middleware called", [
            'host' => $host,
            'path' => $request->path(),
            'url' => $request->url(),
        ]);

        $village = $this->getVillageFromHost($host);

        // Add more debug logging
        Log::info("Village resolution result", [
            'host' => $host,
            'village_found' => $village ? $village->name : 'none',
            'village_slug' => $village ? $village->slug : 'none',
        ]);

        // Share village instance with the request
        $request->attributes->set('village', $village);

        // You can also share it globally for views
        if ($village) {
            view()->share('currentVillage', $village);
        }

        return $next($request);
    }

    /**
     * Get village from the current host
     */
    private function getVillageFromHost(string $host): ?Village
    {
        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        Log::info("Village resolution details", [
            'host' => $host,
            'base_domain' => $baseDomain,
            'checking_subdomain' => str_ends_with($host, '.' . $baseDomain),
        ]);

        // Check if it's a subdomain of the base domain
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);

            Log::info("Subdomain detected", [
                'subdomain' => $subdomain,
                'looking_for_village' => $subdomain,
            ]);

            $village = Village::where('slug', $subdomain)->active()->first();

            Log::info("Village lookup result", [
                'subdomain' => $subdomain,
                'village_found' => $village ? $village->name : 'not found',
            ]);

            return $village;
        }

        // Check if it's a custom domain
        $village = Village::where('domain', $host)->active()->first();

        Log::info("Custom domain lookup", [
            'host' => $host,
            'village_found' => $village ? $village->name : 'not found',
        ]);

        return $village;
    }
}
