<?php

// app/Console/Commands/CleanupUnusedFiles.php
namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Media;
use App\Models\Village;
use App\Models\Article;
use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupUnusedFiles extends Command
{
    protected $signature = 'files:cleanup
                           {--dry-run : Show what would be deleted without actually deleting}
                           {--directory= : Specific directory to clean up}';

    protected $description = 'Clean up unused image and media files';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $directory = $this->option('directory');

        $this->info('Starting file cleanup...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        $directories = $directory ? [$directory] : [
            'villages',
            'articles',
            'products',
            'gallery',
            'media',
            'cache'
        ];

        $totalDeleted = 0;

        foreach ($directories as $dir) {
            $deleted = $this->cleanupDirectory($dir, $dryRun);
            $totalDeleted += $deleted;
        }

        $this->info("Cleanup completed! {$totalDeleted} files " . ($dryRun ? 'would be' : 'were') . " deleted.");

        return 0;
    }

    protected function cleanupDirectory(string $directory, bool $dryRun): int
    {
        $this->info("Cleaning up directory: {$directory}");

        $files = Storage::disk('public')->allFiles($directory);
        $usedFiles = $this->getUsedFiles($directory);
        $unusedFiles = array_diff($files, $usedFiles);

        if (empty($unusedFiles)) {
            $this->info("No unused files found in {$directory}");
            return 0;
        }

        $this->info("Found " . count($unusedFiles) . " unused files in {$directory}");

        if ($this->confirm("Delete these files?") || $dryRun) {
            foreach ($unusedFiles as $file) {
                if ($dryRun) {
                    $this->line("Would delete: {$file}");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("Deleted: {$file}");
                }
            }
        }

        return count($unusedFiles);
    }

    protected function getUsedFiles(string $directory): array
    {
        $usedFiles = [];

        switch ($directory) {
            case 'villages':
                $villages = Village::whereNotNull('image_url')->get();
                foreach ($villages as $village) {
                    $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $village->image_url);
                }
                break;

            case 'articles':
                $articles = Article::whereNotNull('cover_image_url')->get();
                foreach ($articles as $article) {
                    $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $article->cover_image_url);
                }
                break;

            case 'products':
                $offers = Offer::whereNotNull('primary_image_url')->get();
                foreach ($offers as $offer) {
                    $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $offer->primary_image_url);
                }
                break;

            case 'gallery':
                $images = Image::whereNotNull('image_url')->get();
                foreach ($images as $image) {
                    $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $image->image_url);
                }
                break;

            case 'media':
                $mediaFiles = Media::whereNotNull('file_url')->get();
                foreach ($mediaFiles as $media) {
                    $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $media->file_url);
                    if ($media->thumbnail_url) {
                        $usedFiles[] = str_replace(Storage::disk('public')->url(''), '', $media->thumbnail_url);
                    }
                }
                break;
        }

        return array_filter($usedFiles);
    }
}
