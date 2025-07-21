<?php

// app/Console/Commands/GenerateMissingThumbnails.php
namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateMissingThumbnails extends Command
{
    protected $signature = 'thumbnails:generate
                           {--sizes=300x300,150x150 : Comma-separated list of thumbnail sizes}
                           {--force : Regenerate existing thumbnails}';

    protected $description = 'Generate missing thumbnails for all images';

    public function handle(): int
    {
        $sizes = explode(',', $this->option('sizes'));
        $force = $this->option('force');

        $this->info('Generating thumbnails...');

        $images = Image::whereNotNull('image_url')->get();
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        $generated = 0;

        foreach ($images as $image) {
            foreach ($sizes as $size) {
                [$width, $height] = explode('x', trim($size));

                try {
                    $thumbnailGenerated = $this->generateThumbnail(
                        $image,
                        (int) $width,
                        (int) $height,
                        $force
                    );

                    if ($thumbnailGenerated) {
                        $generated++;
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to generate thumbnail for image {$image->id}: " . $e->getMessage());
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$generated} thumbnails");

        return 0;
    }

    protected function generateThumbnail(Image $image, int $width, int $height, bool $force): bool
    {
        $imageService = app(ImageUploadService::class);

        try {
            $thumbnailUrl = $imageService->generateThumbnail(
                $image->image_url,
                $width,
                $height
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
