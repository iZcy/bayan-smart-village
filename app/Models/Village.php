<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'established_at' => 'datetime'
    ];

    // Automatically generate slug from name if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($village) {
            if (empty($village->slug)) {
                $village->slug = static::generateUniqueSlug($village->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Relationships
    public function places(): HasMany
    {
        return $this->hasMany(SmeTourismPlace::class);
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class)->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // Helper methods
    public function getFullDomainAttribute(): string
    {
        if ($this->domain) {
            return $this->domain;
        }

        $baseDomain = config('app.domain', 'kecamatanbayan.id');
        return "{$this->slug}.{$baseDomain}";
    }

    public function getUrlAttribute(): string
    {
        return "https://{$this->full_domain}";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        });
    }
}
