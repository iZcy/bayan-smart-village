<?php

// Model: Media.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Relationships
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

    // Scopes
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

    // Accessors
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->type === 'video';
    }

    public function getIsAudioAttribute(): bool
    {
        return $this->type === 'audio';
    }

    // Helper methods
    public function getDefaultThumbnail(): string
    {
        if ($this->is_video && $this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        // Return default thumbnails based on context
        $defaults = [
            'home' => '/images/default-video-home.jpg',
            'places' => '/images/default-video-places.jpg',
            'products' => '/images/default-video-products.jpg',
            'articles' => '/images/default-video-articles.jpg',
            'gallery' => '/images/default-video-gallery.jpg',
        ];

        return $defaults[$this->context] ?? '/images/default-video.jpg';
    }

    public function canAutoplay(): bool
    {
        return $this->autoplay && $this->is_active;
    }

    public function getVolumeLevel(): float
    {
        return max(0, min(1, $this->volume));
    }

    // Static helper methods
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
