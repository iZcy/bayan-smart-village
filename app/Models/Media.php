<?php

// app/Models/Media.php - Updated with enhanced file handling
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'community_id',
        'sme_id',
        'place_id',
        'title',
        'description',
        'type',
        'context',
        'file_url',
        'thumbnail_url',
        'duration',
        'mime_type',
        'file_size',
        'is_featured',
        'is_active',
        'autoplay',
        'loop',
        'muted',
        'volume',
        'sort_order',
        'settings'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'autoplay' => 'boolean',
        'loop' => 'boolean',
        'muted' => 'boolean',
        'volume' => 'decimal:2',
        'sort_order' => 'integer',
        'duration' => 'integer',
        'file_size' => 'integer',
        'settings' => 'array',
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
        return $this->belongsTo(Place::class);
    }

    // Enhanced file handling methods
    public function getFilePathAttribute(): ?string
    {
        if (!$this->file_url) {
            return null;
        }

        // Handle both old format (/video/file.mp4) and new format (media/file.mp4)
        $filePath = $this->file_url;

        // If the path starts with a slash, it's the old format - remove the slash
        if (str_starts_with($filePath, '/')) {
            $filePath = ltrim($filePath, '/');
        }

        // If the path doesn't start with 'media/', it's an old format - add media prefix
        if (!str_starts_with($filePath, 'media/')) {
            $filePath = 'media/' . $filePath;
        }

        return $filePath;
    }

    /**
     * Get the public URL for the media file
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->file_url) {
            return null;
        }

        // Handle both old format (/video/file.mp4) and new format (media/file.mp4)
        $filePath = $this->file_url;

        // If the path starts with a slash, it's the old format - remove the slash
        if (str_starts_with($filePath, '/')) {
            $filePath = ltrim($filePath, '/');
        }

        // If the path doesn't start with 'media/', it's an old format - add media prefix
        if (!str_starts_with($filePath, 'media/')) {
            $filePath = 'media/' . $filePath;
        }

        return Storage::disk('public')->url($filePath);
    }

    public function getThumbnailPathAttribute(): ?string
    {
        if (!$this->thumbnail_url) {
            return null;
        }

        // Handle both old format (/thumbnails/file.jpg) and new format (media/thumbnails/file.jpg)
        $thumbnailPath = $this->thumbnail_url;

        // If the path starts with a slash, it's the old format - remove the slash
        if (str_starts_with($thumbnailPath, '/')) {
            $thumbnailPath = ltrim($thumbnailPath, '/');
        }

        // If the path doesn't start with 'media/', it's an old format - add media prefix
        if (!str_starts_with($thumbnailPath, 'media/')) {
            $thumbnailPath = 'media/' . $thumbnailPath;
        }

        return $thumbnailPath;
    }

    /**
     * Get the public URL for the thumbnail file
     */
    public function getThumbnailPublicUrlAttribute(): ?string
    {
        if (!$this->thumbnail_url) {
            return null;
        }

        // Handle both old format (/thumbnails/file.jpg) and new format (media/thumbnails/file.jpg)
        $thumbnailPath = $this->thumbnail_url;

        // If the path starts with a slash, it's the old format - remove the slash
        if (str_starts_with($thumbnailPath, '/')) {
            $thumbnailPath = ltrim($thumbnailPath, '/');
        }

        // If the path doesn't start with 'media/', it's an old format - add media prefix
        if (!str_starts_with($thumbnailPath, 'media/')) {
            $thumbnailPath = 'media/' . $thumbnailPath;
        }

        return Storage::disk('public')->url($thumbnailPath);
    }

    public function getFileInfoAttribute(): ?array
    {
        if (!$this->file_url) {
            return null;
        }

        $path = $this->file_path;
        if ($path && Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            $fileSize = Storage::disk('public')->size($path);

            return [
                'path' => $path,
                'full_path' => $fullPath,
                'size' => $fileSize,
                'human_size' => $this->formatFileSize($fileSize),
                'exists' => true,
                'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
                'basename' => basename($fullPath),
            ];
        }

        return ['exists' => false];
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        return $this->formatFileSize($this->file_size);
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function deleteMediaFiles(): bool
    {
        $success = true;

        // Delete main file
        if ($this->file_url) {
            $path = $this->file_path;
            if ($path && Storage::disk('public')->exists($path)) {
                $success = Storage::disk('public')->delete($path) && $success;
            }
        }

        // Delete thumbnail
        if ($this->thumbnail_url) {
            $path = $this->thumbnail_path;
            if ($path && Storage::disk('public')->exists($path)) {
                $success = Storage::disk('public')->delete($path) && $success;
            }
        }

        return $success;
    }

    public function updateFileInfo(): void
    {
        if ($this->file_url) {
            $path = $this->file_path;
            if ($path && Storage::disk('public')->exists($path)) {
                $fullPath = Storage::disk('public')->path($path);

                // Update file size if not set
                if (!$this->file_size) {
                    $this->file_size = Storage::disk('public')->size($path);
                }

                // Update MIME type if not set
                if (!$this->mime_type) {
                    $this->mime_type = Storage::disk('public')->mimeType($path);
                }

                // Try to get duration for video/audio files if not set
                if (!$this->duration && in_array($this->type, ['video', 'audio'])) {
                    $this->duration = $this->extractDuration($fullPath);
                }

                $this->saveQuietly();
            }
        }
    }

    private function extractDuration(string $filePath): ?int
    {
        // This would require FFmpeg or similar tool
        // For now, return null - you can implement this based on your needs
        try {
            // Example using getID3 library (install with: composer require james-heinrich/getid3)
            if (class_exists('\getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);

                if (isset($fileInfo['playtime_seconds'])) {
                    return (int) round($fileInfo['playtime_seconds']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not extract media duration: ' . $e->getMessage());
        }

        return null;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        // Update file info after creating
        static::created(function (Media $media) {
            $media->updateFileInfo();
        });

        // Clean up files when media record is deleted
        static::deleting(function (Media $media) {
            $media->deleteMediaFiles();
        });
    }

    // Existing scopes and methods remain the same...
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    public function scopeForVillage($query, $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    // Static helper methods remain the same...
    public static function getContextOptions(): array
    {
        return [
            'home' => 'Home Page',
            'places' => 'Places Section',
            'products' => 'Products Section',
            'articles' => 'Articles Section',
            'gallery' => 'Gallery Section',
            'global' => 'Global (All Pages)',
        ];
    }

    public static function getTypeOptions(): array
    {
        return [
            'video' => 'Video',
            'audio' => 'Audio',
        ];
    }

    public static function getForContext(string $context, $villageId = null, string $type = null)
    {
        $query = static::active()
            ->where(function ($q) use ($context) {
                $q->where('context', $context)
                    ->orWhere('context', 'global');
            })
            ->ordered();

        if ($villageId) {
            $query->forVillage($villageId);
        }

        if ($type) {
            $query->byType($type);
        }

        return $query->get();
    }

    public static function getFeaturedForContext(string $context, $villageId = null, string $type = null)
    {
        $query = static::active()
            ->featured()
            ->where(function ($q) use ($context) {
                $q->where('context', $context)
                    ->orWhere('context', 'global');
            })
            ->ordered();

        if ($villageId) {
            $query->forVillage($villageId);
        }

        if ($type) {
            $query->byType($type);
        }

        return $query->first();
    }
}
