<?php

namespace App\Services;

use App\Models\Village;

class SmartVillageUrlService
{
    /**
     * Get the appropriate protocol based on environment
     */
    public static function getProtocol(): string
    {
        return config('smartvillage.url.protocol', app()->environment('local') ? 'http' : 'https');
    }

    /**
     * Build a village URL with environment-aware protocol
     */
    public static function buildVillageUrl(Village $village, string $path = ''): string
    {
        $protocol = self::getProtocol();
        $domain = $village->full_domain;
        $path = ltrim($path, '/');

        return "{$protocol}://{$domain}" . ($path ? "/{$path}" : '');
    }

    /**
     * Build an apex domain URL
     */
    public static function buildApexUrl(string $path = ''): string
    {
        $protocol = self::getProtocol();
        $domain = config('app.domain', 'kecamatanbayan.id');
        $path = ltrim($path, '/');

        return "{$protocol}://{$domain}" . ($path ? "/{$path}" : '');
    }

    /**
     * Build a short link URL
     */
    public static function buildShortLinkUrl(?Village $village, string $slug): string
    {
        if ($village) {
            return self::buildVillageUrl($village, "l/{$slug}");
        }

        return self::buildApexUrl("l/{$slug}");
    }

    /**
     * Get test URLs based on environment
     */
    public static function getTestUrls(): array
    {
        $env = app()->environment('local') ? 'local' : 'production';
        return config("smartvillage.short_links.test_urls.{$env}", []);
    }

    /**
     * Check if a URL is a local development URL
     */
    public static function isLocalUrl(string $url): bool
    {
        $localDomains = config('smartvillage.development.local_domains', ['localhost', '127.0.0.1']);

        foreach ($localDomains as $domain) {
            if (str_contains($url, $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get environment-appropriate social media URL
     */
    public static function getSocialMediaUrl(string $platform, string $username): string
    {
        $config = config("smartvillage.services.social_media.{$platform}");

        if (!$config) {
            throw new \InvalidArgumentException("Unsupported social media platform: {$platform}");
        }

        // Use mock URL in local environment if available
        if (app()->environment('local') && isset($config['local_mock'])) {
            return $config['local_mock'] . '/' . $username;
        }

        return $config['base_url'] . $username;
    }

    /**
     * Generate QR code URL for a given URL
     */
    public static function getQrCodeUrl(string $url, int $size = null): string
    {
        $size = $size ?: config('smartvillage.services.qr_code.default_size', '200x200');
        $format = config('smartvillage.services.qr_code.format', 'png');
        $errorCorrection = config('smartvillage.services.qr_code.error_correction', 'M');
        $margin = config('smartvillage.services.qr_code.margin', 10);

        $baseUrl = config('smartvillage.services.qr_code.api_url', 'https://api.qrserver.com/v1/create-qr-code/');

        $params = http_build_query([
            'size' => $size,
            'data' => $url,
            'format' => $format,
            'ecc' => $errorCorrection,
            'margin' => $margin,
        ]);

        return $baseUrl . '?' . $params;
    }

    /**
     * Build environment-appropriate external link for seeding
     */
    public static function buildTestExternalUrl(string $type): string
    {
        $testUrls = self::getTestUrls();

        switch ($type) {
            case 'instagram':
                return app()->environment('local')
                    ? 'http://localhost:8000/test/instagram'
                    : 'https://instagram.com/' . fake()->userName;

            case 'whatsapp':
                return app()->environment('local')
                    ? 'http://localhost:8000/test/whatsapp'
                    : 'https://wa.me/62' . fake()->randomNumber(8, true);

            case 'website':
                return app()->environment('local')
                    ? fake()->randomElement(['http://localhost:3000', 'http://localhost:8080'])
                    : 'https://www.' . fake()->domainName;

            case 'maps':
                return app()->environment('local')
                    ? 'http://localhost:8000/test/maps'
                    : 'https://maps.google.com/search/' . urlencode(fake()->address);

            default:
                return fake()->randomElement($testUrls);
        }
    }

    /**
     * Get environment info for display
     */
    public static function getEnvironmentInfo(): array
    {
        return [
            'environment' => app()->environment(),
            'is_local' => app()->environment('local'),
            'protocol' => self::getProtocol(),
            'show_env_badges' => config('smartvillage.development.show_env_badges', false),
            'debug_mode' => config('smartvillage.development.debug_links', false),
        ];
    }

    /**
     * Validate and format URL with environment-appropriate protocol
     */
    public static function formatUrl(string $url): string
    {
        // If URL already has a protocol, return as-is
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        // Add environment-appropriate protocol
        $protocol = self::getProtocol();
        return "{$protocol}://{$url}";
    }
}
