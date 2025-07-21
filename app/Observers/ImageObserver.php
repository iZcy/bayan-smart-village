<?php

// app/Observers/ImageObserver.php
namespace App\Observers;

use App\Models\Image;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Log;

class ImageObserver
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function deleting(Image $image): void
    {
        // Delete the actual image file when the model is deleted
        if ($image->image_url) {
            try {
                $this->imageUploadService->deleteImage($image->image_url);
            } catch (\Exception $e) {
                // Log error but don't prevent deletion
                Log::warning('Failed to delete image file: ' . $e->getMessage(), [
                    'image_id' => $image->id,
                    'image_url' => $image->image_url
                ]);
            }
        }
    }
}
