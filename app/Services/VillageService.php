<?php

// app/Services/VillageService.php
namespace App\Services;

use App\Models\Village;
use App\Models\ExternalLink;
use App\Models\Offer;
use App\Models\Article;
use App\Helpers\VillageHelper;
use Illuminate\Support\Facades\Cache;

class VillageService
{
    /**
     * Get village statistics
     */
    public function getVillageStatistics(Village $village): array
    {
        return Cache::remember("village_stats:{$village->id}", 600, function () use ($village) {
            return [
                'communities' => $village->communities()->where('is_active', true)->count(),
                'places' => $village->places()->count(),
                'smes' => \App\Models\Sme::whereHas('community', function ($query) use ($village) {
                    $query->where('village_id', $village->id);
                })->where('is_active', true)->count(),
                'products' => Offer::whereHas('sme.community', function ($query) use ($village) {
                    $query->where('village_id', $village->id);
                })->where('is_active', true)->count(),
                'articles' => $village->articles()->where('is_published', true)->count(),
                'external_links' => $village->externalLinks()->where('is_active', true)->count(),
                'images' => $village->images()->count(),
            ];
        });
    }

    /**
     * Get featured content for village
     */
    public function getFeaturedContent(Village $village): array
    {
        return Cache::remember("village_featured:{$village->id}", 300, function () use ($village) {
            return [
                'articles' => $village->articles()
                    ->where('is_published', true)
                    ->where('is_featured', true)
                    ->with(['community', 'sme', 'place'])
                    ->latest('published_at')
                    ->take(3)
                    ->get(),

                'products' => Offer::whereHas('sme.community', function ($query) use ($village) {
                    $query->where('village_id', $village->id);
                })
                    ->where('is_active', true)
                    ->where('is_featured', true)
                    ->with(['sme.community', 'category'])
                    ->latest()
                    ->take(6)
                    ->get(),

                'places' => $village->places()
                    ->with(['images' => function ($query) {
                        $query->where('is_featured', true)->take(1);
                    }])
                    ->latest()
                    ->take(4)
                    ->get(),
            ];
        });
    }

    /**
     * Generate QR code for short link
     */
    public function generateQrCode(string $url, int $size = 200): string
    {
        $qrApiUrl = config('smartvillage.services.qr_code.api_url');
        $qrSize = config('smartvillage.services.qr_code.default_size', '200x200');

        return $qrApiUrl . '?' . http_build_query([
            'size' => $qrSize,
            'data' => $url,
            'format' => 'png',
            'ecc' => 'M',
            'margin' => 10,
        ]);
    }

    /**
     * Create external link with QR code
     */
    public function createExternalLinkWithQr(array $data): ExternalLink
    {
        $link = ExternalLink::create($data);

        // Generate short URL
        $village = $link->village ?? $link->community->village ?? $link->sme->community->village;
        $shortUrl = VillageHelper::generateShortLinkUrl($village, $link->slug);

        // You could store QR code URL or generate it on-demand
        $qrCodeUrl = $this->generateQrCode($shortUrl);

        return $link;
    }
}
