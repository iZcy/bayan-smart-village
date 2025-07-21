<?php

// app/Models/Image.php - Updated with file handling
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'community_id',
        'sme_id',
        'place_id',
        'image_url',
        'caption',
        'alt_text',
        'sort_order',
        'is_featured'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
    ];

    // Relationships remain the same...
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function sme(): BelongsTo
    {
        return $this->belongsTo(Sme::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    // New methods for file handling
    public function getImagePathAttribute(): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        // Extract path from URL
        $baseUrl = Storage::disk('public')->url('');
        return str_replace($baseUrl, '', $this->image_url);
    }

    public function getImageSizeAttribute(): ?array
    {
        if (!$this->image_url) {
            return null;
        }

        $path = $this->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            $imageInfo = getimagesize($fullPath);

            if ($imageInfo) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'mime_type' => $imageInfo['mime'],
                    'file_size' => Storage::disk('public')->size($path)
                ];
            }
        }

        return null;
    }

    public function getThumbnailUrl(int $width = 300, int $height = 300): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        // Use the media serve controller for optimized thumbnails
        $path = $this->image_path;
        if ($path) {
            return route('media.thumbnail', ['path' => $path]) . "?w={$width}&h={$height}";
        }

        return null;
    }

    public function getOptimizedUrl(int $quality = 85, ?int $width = null, ?int $height = null): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        $path = $this->image_path;
        if ($path) {
            $params = ['q' => $quality];
            if ($width) $params['w'] = $width;
            if ($height) $params['h'] = $height;

            return route('media.optimized', ['path' => $path]) . '?' . http_build_query($params);
        }

        return null;
    }

    public function deleteImageFile(): bool
    {
        if (!$this->image_url) {
            return false;
        }

        $path = $this->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    // Boot method to handle file operations
    protected static function boot()
    {
        parent::boot();

        // Clean up files when image record is deleted
        static::deleting(function (Image $image) {
            $image->deleteImageFile();
        });
    }
}
