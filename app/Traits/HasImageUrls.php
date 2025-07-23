<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasImageUrls
{
    /**
     * Convert a potentially local image path to a full URL
     * 
     * @param string|null $imagePath
     * @return string|null
     */
    protected function convertImagePathToUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        // If the path already contains http/https, it's already a full URL
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        // Handle local paths
        $filePath = $imagePath;

        // If the path starts with a slash, remove it
        if (str_starts_with($filePath, '/')) {
            $filePath = ltrim($filePath, '/');
        }

        // The path should be used as-is since it's already relative to the public storage disk
        // Examples: "products/filename.jpg", "articles/filename.jpg", etc.
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Get logo URL with domain conversion
     */
    public function getLogoUrlAttribute($value): ?string
    {
        return $this->convertImagePathToUrl($value);
    }

    /**
     * Get image URL with domain conversion
     */
    public function getImageUrlAttribute($value): ?string
    {
        return $this->convertImagePathToUrl($value);
    }

    /**
     * Get cover image URL with domain conversion
     */
    public function getCoverImageUrlAttribute($value): ?string
    {
        return $this->convertImagePathToUrl($value);
    }

    /**
     * Get primary image URL with domain conversion
     */
    public function getPrimaryImageUrlAttribute($value): ?string
    {
        return $this->convertImagePathToUrl($value);
    }
}