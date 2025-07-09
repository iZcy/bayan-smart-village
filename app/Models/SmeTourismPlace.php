<?php
// app/Models/SmeTourismPlace.php - Updated

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SmeTourismPlace extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone_number',
        'image_url',
        'category_id',
        'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($place) {
            if (empty($place->slug)) {
                $place->slug = static::generateUniqueSlug($place->name, $place->village_id);
            }
        });

        static::updating(function ($place) {
            if ($place->isDirty('name')) {
                $place->slug = static::generateUniqueSlug($place->name, $place->village_id, $place->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?string $villageId = null, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('village_id', $villageId)->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('village_id', $villageId)->where('slug', $slug);

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            $counter++;
        }

        return $slug;
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'place_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'place_id');
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class, 'place_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'place_id');
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function featuredProducts(): HasMany
    {
        return $this->products()->where('is_featured', true)->where('is_active', true);
    }

    // Get the place URL
    public function getUrlAttribute(): string
    {
        $baseUrl = $this->village ? $this->village->url : config('app.url');
        return "{$baseUrl}/places/{$this->slug}";
    }

    // Get display coordinates
    public function getCoordinatesAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude}, {$this->longitude}";
        }
        return null;
    }

    // Check if place has location data
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // Get place type based on category
    public function getTypeAttribute(): string
    {
        return $this->category?->type ?? 'unknown';
    }
}
