<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixMediaMetadata extends Command
{
    protected $signature = 'media:fix-metadata {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix media records with missing or incorrect metadata (duration, file size, MIME type)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning media records for missing metadata...');
        
        $mediaRecords = Media::where(function($query) {
            $query->whereNull('duration')
                  ->orWhereNull('file_size')
                  ->orWhereNull('mime_type')
                  ->orWhere('duration', 0)
                  ->orWhere('file_size', 0);
        })->get();
        
        if ($mediaRecords->isEmpty()) {
            $this->info('No media records need fixing.');
            return;
        }
        
        $this->info("Found {$mediaRecords->count()} media records that need fixing.");
        
        $fixed = 0;
        $errors = 0;
        
        foreach ($mediaRecords as $media) {
            $this->line("Processing: {$media->title} (ID: {$media->id})");
            
            // Check if file exists
            if (!Storage::disk('public')->exists($media->file_url)) {
                $this->error("  ❌ File not found: {$media->file_url}");
                $errors++;
                continue;
            }
            
            if ($dryRun) {
                $this->info("  🔍 Would update metadata for: {$media->file_url}");
                $fixed++;
                continue;
            }
            
            try {
                $media->updateFileInfo();
                $this->info("  ✅ Updated - Duration: {$media->duration}s, Size: {$media->file_size} bytes, MIME: {$media->mime_type}");
                $fixed++;
            } catch (\Exception $e) {
                $this->error("  ❌ Failed to update: " . $e->getMessage());
                $errors++;
            }
        }
        
        if ($dryRun) {
            $this->info("\n📋 Dry run completed:");
            $this->info("  - Would fix: {$fixed} records");
            $this->info("  - Errors: {$errors} records");
            $this->info("\nRun without --dry-run to apply changes.");
        } else {
            $this->info("\n✨ Metadata fix completed:");
            $this->info("  - Fixed: {$fixed} records");
            $this->info("  - Errors: {$errors} records");
        }
    }
}