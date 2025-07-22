<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckMediaFiles extends Command
{
    protected $signature = 'media:check-files';
    protected $description = 'Check which media files exist and which are missing';

    public function handle()
    {
        $media = Media::all();
        
        $this->info("Checking {$media->count()} media records...");
        
        $existing = 0;
        $missing = 0;
        
        foreach ($media as $m) {
            if (Storage::disk('public')->exists($m->file_path)) {
                $existing++;
                $this->line("✅ {$m->title} - {$m->file_path}");
            } else {
                $missing++;
                $this->error("❌ {$m->title} - {$m->file_path} (MISSING)");
            }
        }
        
        $this->info("\nSummary:");
        $this->info("- Existing files: {$existing}");
        $this->info("- Missing files: {$missing}");
    }
}