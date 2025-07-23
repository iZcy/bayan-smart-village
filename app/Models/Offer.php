<?php

// Model: Offer.php

namespace App\Models;

use App\Traits\HasImageUrls;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Offer extends Model
{
    use HasFactory, HasImageUrls, HasUuids;

    protected $fillable = [
        'sme_id',
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
        'seasonal_availability' => 'array',
        'materials' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'features' => 'array',
        'certification' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    public function sme(): BelongsTo
    {
        return $this->belongsTo(Sme::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ecommerceLinks(): HasMany
    {
        return $this->hasMany(OfferEcommerceLink::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(OfferImage::class)->ordered();
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(OfferImage::class)->where('is_primary', true);
    }

    public function additionalImages(): HasMany
    {
        return $this->hasMany(OfferImage::class)->where('is_primary', false)->ordered();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(OfferTag::class, 'offer_tag_pivot', 'offer_id', 'offer_tag_id');
    }

    /**
     * Get the primary image URL - prioritizes primary_image_url field over relationship
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        // First, try to get from the direct primary_image_url field
        if (! empty($this->attributes['primary_image_url'])) {
            // Use the trait's URL conversion method to ensure full URL
            return $this->convertImagePathToUrl($this->attributes['primary_image_url']);
        }

        // Fallback to primary image relationship (for backward compatibility)
        if ($this->relationLoaded('primaryImage') && $this->primaryImage) {
            return $this->primaryImage->image_url;
        }

        // If not loaded, try to load the relationship
        if (! $this->relationLoaded('primaryImage')) {
            $primaryImage = $this->primaryImage()->first();
            if ($primaryImage) {
                return $primaryImage->image_url;
            }
        }

        // Final fallback: use the first available image from the images relationship
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->first()->image_url;
        }

        // If images not loaded, try to load and get first image
        if (! $this->relationLoaded('images')) {
            $firstImage = $this->images()->first();
            if ($firstImage) {
                return $firstImage->image_url;
            }
        }

        return null;
    }
}
