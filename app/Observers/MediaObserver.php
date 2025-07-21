<?php

// app/Observers/MediaObserver.php
namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public function deleting(Media $media): void
    {
        // Delete the actual media files when the model is deleted
        if ($media->file_url) {
            try {
                $path = str_replace(Storage::disk('public')->url(''), '', $media->file_url);
                Storage::disk('public')->delete($path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete media file: ' . $e->getMessage(), [
                    'media_id' => $media->id,
                    'file_url' => $media->file_url
                ]);
            }
        }

        if ($media->thumbnail_url) {
            try {
                $path = str_replace(Storage::disk('public')->url(''), '', $media->thumbnail_url);
                Storage::disk('public')->delete($path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete thumbnail file: ' . $e->getMessage(), [
                    'media_id' => $media->id,
                    'thumbnail_url' => $media->thumbnail_url
                ]);
            }
        }
    }
}
