<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigrateMediaFiles extends Command
{
    protected $signature = 'media:migrate-files';
    protected $description = 'Migrate media files from public directory to storage and update URLs';

    public function handle()
    {
        $media = Media::all();
        $this->info("Migrating {$media->count()} media files...");
        
        $migrated = 0;
        $skipped = 0;
        $failed = 0;
        
        foreach ($media as $m) {
            $this->line("Processing: {$m->title}");
            
            // Get the current file path (old location)
            $oldPath = ltrim($m->file_url, '/'); // Remove leading slash
            $oldFullPath = public_path($oldPath);
            
            // Check if old file exists
            if (!File::exists($oldFullPath)) {
                $this->warn("  ⚠️  File not found: {$oldFullPath}");
                $skipped++;
                continue;
            }
            
            // Create new path
            $newPath = 'media/' . $oldPath;
            
            // Check if new file already exists
            if (Storage::disk('public')->exists($newPath)) {
                $this->line("  ✅ Already exists in new location: {$newPath}");
                $skipped++;
                continue;
            }
            
            // Create directory if it doesn't exist
            $newDir = dirname($newPath);
            if (!Storage::disk('public')->exists($newDir)) {
                Storage::disk('public')->makeDirectory($newDir);
                $this->line("  📁 Created directory: {$newDir}");
            }
            
            // Copy file to new location
            try {
                $fileContent = File::get($oldFullPath);
                Storage::disk('public')->put($newPath, $fileContent);
                
                $this->info("  ✅ Migrated: {$oldPath} → {$newPath}");
                $migrated++;
                
                // Handle thumbnail if exists
                if ($m->thumbnail_url) {
                    $oldThumbnailPath = ltrim($m->thumbnail_url, '/');
                    $oldThumbnailFullPath = public_path($oldThumbnailPath);
                    
                    if (File::exists($oldThumbnailFullPath)) {
                        $newThumbnailPath = 'media/' . $oldThumbnailPath;
                        
                        // Create thumbnail directory if needed
                        $thumbnailDir = dirname($newThumbnailPath);
                        if (!Storage::disk('public')->exists($thumbnailDir)) {
                            Storage::disk('public')->makeDirectory($thumbnailDir);
                        }
                        
                        $thumbnailContent = File::get($oldThumbnailFullPath);
                        Storage::disk('public')->put($newThumbnailPath, $thumbnailContent);
                        $this->line("  🖼️  Migrated thumbnail: {$oldThumbnailPath} → {$newThumbnailPath}");
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Failed to migrate {$oldPath}: " . $e->getMessage());
                $failed++;
            }
        }
        
        $this->info("\n📊 Migration Summary:");
        $this->info("- Migrated: {$migrated}");
        $this->info("- Skipped: {$skipped}");
        $this->info("- Failed: {$failed}");
        
        if ($migrated > 0) {
            $this->info("\n🎉 Migration completed! The URL generation in Media model will now work correctly.");
            $this->warn("💡 You can now delete the old files from public/video and public/audio directories if everything works correctly.");
        }
    }
}
