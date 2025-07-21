<?php

// app/Services/ImageUploadService.php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadService
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    protected int $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function uploadImage(
        UploadedFile $file,
        string $directory = 'general',
        string $disk = 'public',
        array $resizeOptions = []
    ): string {
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Create full path
        $path = $directory . '/' . $filename;

        // Process image if resize options are provided
        if (!empty($resizeOptions) && $this->shouldResize($file)) {
            $processedImage = $this->processImage($file, $resizeOptions);
            Storage::disk($disk)->put($path, $processedImage);
        } else {
            // Store original file
            Storage::disk($disk)->putFileAs($directory, $file, $filename);
        }

        // Return full URL
        return Storage::disk($disk)->url($path);
    }

    public function uploadMultipleImages(
        array $files,
        string $directory = 'general',
        string $disk = 'public',
        array $resizeOptions = []
    ): array {
        $urls = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $urls[] = $this->uploadImage($file, $directory, $disk, $resizeOptions);
            }
        }

        return $urls;
    }

    public function deleteImage(string $url, string $disk = 'public'): bool
    {
        // Extract path from URL
        $path = $this->extractPathFromUrl($url);

        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    protected function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid file type. Only images are allowed.');
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException('File size too large. Maximum size is ' . ($this->maxFileSize / 1024 / 1024) . 'MB.');
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    protected function shouldResize(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ]);
    }

    protected function processImage(UploadedFile $file, array $options): string
    {
        $image = Image::make($file->getRealPath());

        // Apply resize options
        if (isset($options['width']) || isset($options['height'])) {
            $image->resize(
                $options['width'] ?? null,
                $options['height'] ?? null,
                function ($constraint) use ($options) {
                    if ($options['maintain_aspect'] ?? true) {
                        $constraint->aspectRatio();
                    }
                    if ($options['prevent_upsizing'] ?? true) {
                        $constraint->upsize();
                    }
                }
            );
        }

        // Apply quality setting
        if (isset($options['quality'])) {
            $image->encode(null, $options['quality']);
        }

        return $image->encode()->toString();
    }

    protected function extractPathFromUrl(string $url): ?string
    {
        // Remove base URL to get relative path
        $baseUrl = config('app.url') . '/storage/';

        if (str_starts_with($url, $baseUrl)) {
            return str_replace($baseUrl, '', $url);
        }

        return null;
    }

    public function getImageDimensions(string $url): ?array
    {
        $path = $this->extractPathFromUrl($url);

        if ($path && Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            $imageInfo = getimagesize($fullPath);

            if ($imageInfo) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'mime_type' => $imageInfo['mime']
                ];
            }
        }

        return null;
    }

    public function generateThumbnail(
        string $originalUrl,
        int $width = 300,
        int $height = 300,
        string $disk = 'public'
    ): string {
        $originalPath = $this->extractPathFromUrl($originalUrl);

        if (!$originalPath || !Storage::disk($disk)->exists($originalPath)) {
            throw new \InvalidArgumentException('Original image not found');
        }

        // Generate thumbnail path
        $pathInfo = pathinfo($originalPath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . "_{$width}x{$height}." . $pathInfo['extension'];

        // Check if thumbnail already exists
        if (Storage::disk($disk)->exists($thumbnailPath)) {
            return Storage::disk($disk)->url($thumbnailPath);
        }

        // Create thumbnail directory if it doesn't exist
        $thumbnailDir = dirname($thumbnailPath);
        if (!Storage::disk($disk)->exists($thumbnailDir)) {
            Storage::disk($disk)->makeDirectory($thumbnailDir);
        }

        // Create thumbnail
        $originalFullPath = Storage::disk($disk)->path($originalPath);
        $image = Image::make($originalFullPath);

        $image->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        // Save thumbnail
        Storage::disk($disk)->put($thumbnailPath, $image->encode()->toString());

        return Storage::disk($disk)->url($thumbnailPath);
    }
}
