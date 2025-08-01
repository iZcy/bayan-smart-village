<?php

// Model: OfferImage.php

namespace App\Models;

use App\Traits\HasImageUrls;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferImage extends Model
{
    use HasFactory, HasImageUrls, HasUuids;

    protected $fillable = [
        'offer_id',
        'image_url',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Scope for ordered images
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
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
