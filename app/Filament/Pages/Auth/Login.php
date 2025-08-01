<?php

namespace App\Filament\Pages\Auth;

use App\Models\Village;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{

    public function authenticate(): ?LoginResponse
    {
        try {
            // Call parent authenticate method first
            $response = parent::authenticate();
            
            // If authentication succeeded, check domain access
            $user = Auth::user();
            
            if ($user && !$this->canUserAccessCurrentDomain($user)) {
                Auth::logout();
                
                $host = request()->getHost();
                $baseDomain = config('app.domain', 'kecamatanbayan.id');
                $isMainDomain = ($host === $baseDomain);
                
                if ($isMainDomain) {
                    throw ValidationException::withMessages([
                        'data.email' => 'You do not have permission to access the main admin panel. Only super administrators can access this domain.',
                    ]);
                } else {
                    throw ValidationException::withMessages([
                        'data.email' => 'You do not have permission to access this village admin panel. Please contact your administrator.',
                    ]);
                }
            }
            
            return $response;
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    /**
     * Check if user can access the current domain
     */
    private function canUserAccessCurrentDomain($user): bool
    {
        $host = request()->getHost();
        $baseDomain = config('app.domain', 'kecamatanbayan.id');
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

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}