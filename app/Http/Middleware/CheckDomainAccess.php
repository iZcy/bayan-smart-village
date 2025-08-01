<?php

namespace App\Http\Middleware;

use App\Models\Village;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckDomainAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply this check to authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $host = $request->getHost();
        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        // Determine if this is the main domain or a village subdomain/custom domain
        $isMainDomain = ($host === $baseDomain);
        $village = null;

        if (!$isMainDomain) {
            // Try to resolve village from subdomain or custom domain
            if (str_ends_with($host, '.' . $baseDomain)) {
                // Extract village slug from subdomain
                $villageSlug = str_replace('.' . $baseDomain, '', $host);
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
        }

        // Check domain access permissions
        if (!$this->userCanAccessDomain($user, $isMainDomain, $village)) {
            // Log the user out and redirect to appropriate login
            Auth::logout();
            
            if ($isMainDomain) {
                return redirect()->route('filament.admin.auth.login')
                    ->withErrors(['email' => 'You do not have permission to access the main admin panel.']);
            } else {
                return redirect()->route('filament.admin.auth.login')
                    ->withErrors(['email' => 'You do not have permission to access this village admin panel.']);
            }
        }

        return $next($request);
    }

    /**
     * Check if user can access the given domain
     */
    private function userCanAccessDomain($user, bool $isMainDomain, $village = null): bool
    {
        // Super admin can access main domain only
        if ($user->isSuperAdmin()) {
            return $isMainDomain;
        }

        // For village subdomains/custom domains
        if (!$isMainDomain && $village) {
            // Village admin can only access their village's subdomain
            if ($user->isVillageAdmin()) {
                return $user->village_id === $village->id;
            }

            // Community admin can access their village's subdomain
            if ($user->isCommunityAdmin() && $user->community) {
                return $user->community->village_id === $village->id;
            }

            // SME admin can access their village's subdomain
            if ($user->isSmeAdmin() && $user->sme && $user->sme->community) {
                return $user->sme->community->village_id === $village->id;
            }
        }

        // If this is the main domain, only super admin can access
        if ($isMainDomain) {
            return $user->isSuperAdmin();
        }

        return false;
    }
}
