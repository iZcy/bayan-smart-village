<?php

// Model: OfferImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferImage extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'offer_id',
        'image_url',
        'alt_text',
        'sort_order',
        'is_primary'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Scope for primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for ordered images
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Boot method to handle primary image logic
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($offerImage) {
            // If this image is being set as primary, unset other primary images for the same offer
            if ($offerImage->is_primary) {
                static::where('offer_id', $offerImage->offer_id)
                    ->where('id', '!=', $offerImage->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Get the file name from URL
     */
    public function getFileNameAttribute(): string
    {
        return basename($this->image_url);
    }

    /**
     * Get image dimensions if available (you might want to store these)
     */
    public function getDimensionsAttribute(): ?array
    {
        // This would require additional fields in the database
        // or external service to get image dimensions
        return null;
    }
}
