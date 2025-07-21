<?php

// app/Traits/HasImageUpload.php
namespace App\Traits;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImageUpload
{
    /**
     * Upload image and return URL
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory = "",
        array $resizeOptions = []
    ): string {
        $imageService = app(ImageUploadService::class);
        $directory = $directory ?? $this->getImageDirectory();

        return $imageService->uploadImage($file, $directory, 'public', $resizeOptions);
    }

    /**
     * Get default image directory for this model
     */
    protected function getImageDirectory(): string
    {
        return strtolower(class_basename($this)) . 's';
    }

    /**
     * Delete image file from storage
     */
    public function deleteImageFile(string $imageUrl): bool
    {
        $imageService = app(ImageUploadService::class);
        return $imageService->deleteImage($imageUrl);
    }

    /**
     * Get optimized image URL
     */
    public function getOptimizedImageUrl(string $imageUrl, array $options = []): string
    {
        if (empty($imageUrl)) {
            return '';
        }

        $defaults = [
            'quality' => 85,
            'width' => null,
            'height' => null
        ];

        $options = array_merge($defaults, $options);

        $path = str_replace(Storage::disk('public')->url(''), '', $imageUrl);
        $params = array_filter([
            'q' => $options['quality'],
            'w' => $options['width'],
            'h' => $options['height']
        ]);

        return route('media.optimized', ['path' => $path]) . '?' . http_build_query($params);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $imageUrl, int $width = 300, int $height = 300): string
    {
        if (empty($imageUrl)) {
            return '';
        }

        $path = str_replace(Storage::disk('public')->url(''), '', $imageUrl);
        return route('media.thumbnail', ['path' => $path]) . "?w={$width}&h={$height}";
    }
}
