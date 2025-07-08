<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'place_id',
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'price_unit',
        'price_range_min',
        'price_range_max',
        'availability',
        'seasonal_availability',
        'primary_image_url',
        'materials',
        'colors',
        'sizes',
        'features',
        'certification',
        'production_time',
        'minimum_order',
        'is_featured',
        'is_active',
        'view_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_range_min' => 'decimal:2',
        'price_range_max' => 'decimal:2',
        'minimum_order' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'seasonal_availability' => 'array',
        'materials' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'features' => 'array',
        'certification' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->village_id);
            }

            // Auto-assign village_id based on place_id if not set
            if (!$product->village_id && $product->place_id) {
                $place = SmeTourismPlace::find($product->place_id);
                if ($place && $place->village_id) {
                    $product->village_id = $place->village_id;
                }
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->village_id, $product->id);
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

    // Relationships
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ecommerceLinks(): HasMany
    {
        return $this->hasMany(ProductEcommerceLink::class)->orderBy('sort_order');
    }

    public function activeEcommerceLinks(): HasMany
    {
        return $this->ecommerceLinks()->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag_pivot', 'product_id', 'product_tag_id')
            ->withTimestamps();
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

    public function scopeAvailable($query)
    {
        return $query->where('availability', 'available');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByVillage($query, $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    public function scopeByPlace($query, $placeId)
    {
        return $query->where('place_id', $placeId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%");
        });
    }

    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where(function ($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice)
                    ->orWhere('price_range_min', '>=', $minPrice);
            });
        }

        if ($maxPrice !== null) {
            $query->where(function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice)
                    ->orWhere('price_range_max', '<=', $maxPrice);
            });
        }

        return $query;
    }

    // Helper methods
    public function getDisplayPriceAttribute(): string
    {
        if ($this->price) {
            $formatted = 'Rp ' . number_format($this->price, 0, ',', '.');
            if ($this->price_unit) {
                $formatted .= ' / ' . $this->price_unit;
            }
            return $formatted;
        }

        if ($this->price_range_min && $this->price_range_max) {
            return 'Rp ' . number_format($this->price_range_min, 0, ',', '.') .
                ' - Rp ' . number_format($this->price_range_max, 0, ',', '.');
        }

        if ($this->price_range_min) {
            return 'Mulai dari Rp ' . number_format($this->price_range_min, 0, ',', '.');
        }

        return 'Hubungi untuk harga';
    }

    public function getLowestPriceAttribute(): ?float
    {
        if ($this->price) {
            return $this->price;
        }

        if ($this->price_range_min) {
            return $this->price_range_min;
        }

        // Check e-commerce links for lowest price
        $lowestPlatformPrice = $this->activeEcommerceLinks()
            ->whereNotNull('price_on_platform')
            ->min('price_on_platform');

        return $lowestPlatformPrice;
    }

    public function getAvailabilityStatusAttribute(): string
    {
        return match ($this->availability) {
            'available' => 'Tersedia',
            'out_of_stock' => 'Stok Habis',
            'seasonal' => 'Musiman',
            'on_demand' => 'Pre-order',
            default => 'Tidak Diketahui'
        };
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function getUrlAttribute(): string
    {
        $baseUrl = $this->village ? $this->village->url : config('app.url');
        return "{$baseUrl}/products/{$this->slug}";
    }

    public function getShareUrlAttribute(): string
    {
        return $this->url;
    }
}
