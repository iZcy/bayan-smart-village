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
        'seasonal_availability' => 'json',
        'materials' => 'json',
        'colors' => 'json',
        'sizes' => 'json',
        'features' => 'json',
        'certification' => 'json',
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

    public function additionalImages(): HasMany
    {
        return $this->hasMany(OfferImage::class)->ordered();
    }

    public function images(): HasMany
    {
        // Alias for additionalImages to maintain compatibility
        return $this->additionalImages();
    }

    public function primaryImage(): HasOne
    {
        // Legacy compatibility - returns the first additional image as "primary"
        // Note: Primary image is now stored in primary_image_url field directly
        return $this->hasOne(OfferImage::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(OfferTag::class, 'offer_tag_pivot', 'offer_id', 'offer_tag_id');
    }

    /**
     * Mutators to ensure array fields are properly handled
     */
    public function setSeasonalAvailabilityAttribute($value)
    {
        $this->attributes['seasonal_availability'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setMaterialsAttribute($value)
    {
        $this->attributes['materials'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setColorsAttribute($value)
    {
        $this->attributes['colors'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setSizesAttribute($value)
    {
        $this->attributes['sizes'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setCertificationAttribute($value)
    {
        $this->attributes['certification'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }
}
