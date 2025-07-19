<?php

// Model: Offer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    use HasUuids;

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
        'view_count'
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
        return $this->hasMany(OfferImage::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(OfferTag::class, 'offer_tag_pivot', 'offer_id', 'offer_tag_id');
    }
}
