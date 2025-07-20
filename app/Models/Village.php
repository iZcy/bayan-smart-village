<?php

// Model: Village.php (Updated)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'domain',
        'latitude',
        'longitude',
        'phone_number',
        'email',
        'address',
        'image_url',
        'settings',
        'is_active',
        'established_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'established_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // New media relationship
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    // Helper methods for media
    public function getBackgroundVideoForContext(string $context)
    {
        return $this->media()
            ->where('type', 'video')
            ->where(function ($query) use ($context) {
                $query->where('context', $context)
                    ->orWhere('context', 'global');
            })
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
    }

    public function getBackgroundAudioForContext(string $context)
    {
        return $this->media()
            ->where('type', 'audio')
            ->where(function ($query) use ($context) {
                $query->where('context', $context)
                    ->orWhere('context', 'global');
            })
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
    }

    public function getAllMediaForContext(string $context)
    {
        return $this->media()
            ->where(function ($query) use ($context) {
                $query->where('context', $context)
                    ->orWhere('context', 'global');
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMediaStatistics(): array
    {
        $media = $this->media()->where('is_active', true);

        return [
            'total' => $media->count(),
            'videos' => $media->where('type', 'video')->count(),
            'audios' => $media->where('type', 'audio')->count(),
            'featured' => $media->where('is_featured', true)->count(),
            'by_context' => $media->selectRaw('context, COUNT(*) as count')
                ->groupBy('context')
                ->pluck('count', 'context')
                ->toArray(),
        ];
    }
}
