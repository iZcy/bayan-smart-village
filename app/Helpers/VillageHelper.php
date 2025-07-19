<?php

// app/Helpers/VillageHelper.php
namespace App\Helpers;

use App\Models\Village;
use Illuminate\Support\Facades\Cache;

class VillageHelper
{
    /**
     * Get village from subdomain or custom domain
     */
    public static function getVillageFromHost(string $host): ?Village
    {
        $baseDomain = config('app.domain', 'kecamatanbayan.id');

        // Check if this is a village subdomain
        if (str_ends_with($host, '.' . $baseDomain)) {
            $villageSlug = str_replace('.' . $baseDomain, '', $host);

            return Cache::remember("village:subdomain:{$villageSlug}", 3600, function () use ($villageSlug) {
                return Village::where('slug', $villageSlug)
                    ->where('is_active', true)
                    ->first();
            });
        }

        // Check if this is a custom domain
        return Cache::remember("village:domain:{$host}", 3600, function () use ($host) {
            return Village::where('domain', $host)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Generate village URL
     */
    public static function generateVillageUrl(Village $village, string $path = ''): string
    {
        $baseDomain = config('app.domain', 'kecamatanbayan.id');
        $protocol = config('smartvillage.url.protocol', 'https');

        if ($village->domain) {
            $url = $protocol . '://' . $village->domain;
        } else {
            $url = $protocol . '://' . $village->slug . '.' . $baseDomain;
        }

        return $url . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Generate short link URL
     */
    public static function generateShortLinkUrl(Village $village, string $slug): string
    {
        return self::generateVillageUrl($village, '/l/' . $slug);
    }

    /**
     * Clear village cache
     */
    public static function clearVillageCache(Village $village): void
    {
        Cache::forget("village:subdomain:{$village->slug}");

        if ($village->domain) {
            Cache::forget("village:domain:{$village->domain}");
        }

        Cache::forget("village_links:{$village->id}");
    }
}
