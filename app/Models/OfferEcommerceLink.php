<?php

// Model: OfferEcommerceLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferEcommerceLink extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'offer_id',
        'platform',
        'store_name',
        'product_url',
        'price_on_platform',
        'is_verified',
        'is_active',
        'sort_order',
        'click_count',
        'last_verified_at'
    ];

    protected $casts = [
        'price_on_platform' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'last_verified_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Get platform display name
     */
    public function getPlatformDisplayNameAttribute(): string
    {
        return match ($this->platform) {
            'tokopedia' => 'Tokopedia',
            'shopee' => 'Shopee',
            'tiktok_shop' => 'TikTok Shop',
            'bukalapak' => 'Bukalapak',
            'blibli' => 'Blibli',
            'lazada' => 'Lazada',
            'instagram' => 'Instagram',
            'whatsapp' => 'WhatsApp',
            'website' => 'Website',
            'other' => 'Other',
            default => ucfirst($this->platform),
        };
    }

    /**
     * Scope for active links
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified links
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Increment click count
     */
    public function incrementClickCount(): void
    {
        $this->increment('click_count');
    }
}
