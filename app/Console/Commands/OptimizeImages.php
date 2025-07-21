<?php

// app/Console/Commands/OptimizeImages.php
namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Media;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
                           {--model= : Specific model to optimize (Image, Media)}
                           {--regenerate : Regenerate all thumbnails}
                           {--quality=85 : JPEG quality for optimization}';

    protected $description = 'Optimize images and generate thumbnails';

    public function handle(): int
    {
        $model = $this->option('model');
        $regenerate = $this->option('regenerate');
        $quality = (int) $this->option('quality');

        $this->info('Starting image optimization...');

        if (!$model || $model === 'Image') {
            $this->optimizeImages($regenerate, $quality);
        }

        if (!$model || $model === 'Media') {
            $this->optimizeMedia($regenerate, $quality);
        }

        $this->info('Image optimization completed!');
        return 0;
    }

    protected function optimizeImages(bool $regenerate, int $quality): void
    {
        $images = Image::whereNotNull('image_url')->get();
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $image) {
            try {
                $this->optimizeImageRecord($image, $regenerate, $quality);
            } catch (\Exception $e) {
                $this->error("Failed to optimize image {$image->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Optimized {$images->count()} images");
    }

    protected function optimizeMedia(bool $regenerate, int $quality): void
    {
        $mediaFiles = Media::where('type', 'video')
            ->whereNotNull('file_url')
            ->get();

        $bar = $this->output->createProgressBar($mediaFiles->count());
        $bar->start();

        foreach ($mediaFiles as $media) {
            try {
                $this->generateVideoThumbnail($media, $regenerate);
            } catch (\Exception $e) {
                $this->error("Failed to generate thumbnail for media {$media->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$mediaFiles->count()} video files");
    }

    protected function optimizeImageRecord(Image $image, bool $regenerate, int $quality): void
    {
        if (!$image->image_url) {
            return;
        }

        $path = str_replace(Storage::disk('public')->url(''), '', $image->image_url);

        if (!Storage::disk('public')->exists($path)) {
            $this->warn("Image file not found: {$path}");
            return;
        }

        // Generate thumbnails if they don't exist or if regenerating
        $thumbnailPath = $this->getThumbnailPath($path);

        if ($regenerate || !Storage::disk('public')->exists($thumbnailPath)) {
            $imageService = app(ImageUploadService::class);
            $imageService->generateThumbnail($image->image_url, 300, 300);
        }
    }

    protected function generateVideoThumbnail(Media $media, bool $regenerate): void
    {
        // This would require FFmpeg to generate video thumbnails
        // For now, we'll just check if thumbnail exists
        if ($media->thumbnail_url) {
            $path = str_replace(Storage::disk('public')->url(''), '', $media->thumbnail_url);

            if (Storage::disk('public')->exists($path)) {
                return; // Thumbnail already exists
            }
        }

        // Here you would implement video thumbnail generation
        // using FFmpeg or similar tool
        $this->warn("Video thumbnail generation requires FFmpeg implementation for media {$media->id}");
    }

    protected function getThumbnailPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_300x300.' . $pathInfo['extension'];
    }
}
