<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductEcommerceLink extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'product_id',
        'platform',
        'store_name',
        'product_url',
        'price_on_platform',
        'is_verified',
        'is_active',
        'sort_order',
        'click_count',
        'last_verified_at',
    ];

    protected $casts = [
        'price_on_platform' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'last_verified_at' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Helper methods
    public function getPlatformNameAttribute(): string
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
            'other' => 'Lainnya',
            default => ucfirst($this->platform)
        };
    }

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'tokopedia' => 'tokopedia',
            'shopee' => 'shopee',
            'tiktok_shop' => 'tiktok',
            'bukalapak' => 'bukalapak',
            'blibli' => 'blibli',
            'lazada' => 'lazada',
            'instagram' => 'instagram',
            'whatsapp' => 'whatsapp',
            'website' => 'website',
            'other' => 'link',
            default => 'link'
        };
    }

    public function getPlatformColorAttribute(): string
    {
        return match ($this->platform) {
            'tokopedia' => 'success', // Green
            'shopee' => 'warning', // Orange
            'tiktok_shop' => 'danger', // Red/Pink
            'bukalapak' => 'info', // Blue
            'blibli' => 'primary', // Blue
            'lazada' => 'warning', // Orange
            'instagram' => 'secondary', // Purple
            'whatsapp' => 'success', // Green
            'website' => 'primary', // Blue
            'other' => 'gray',
            default => 'gray'
        };
    }

    public function getCallToActionAttribute(): string
    {
        return match ($this->platform) {
            'tokopedia' => 'Beli di Tokopedia',
            'shopee' => 'Beli di Shopee',
            'tiktok_shop' => 'Beli di TikTok Shop',
            'bukalapak' => 'Beli di Bukalapak',
            'blibli' => 'Beli di Blibli',
            'lazada' => 'Beli di Lazada',
            'instagram' => 'Lihat di Instagram',
            'whatsapp' => 'Pesan via WhatsApp',
            'website' => 'Kunjungi Website',
            'other' => 'Lihat Produk',
            default => 'Lihat Produk'
        };
    }

    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->price_on_platform) {
            return 'Rp ' . number_format($this->price_on_platform, 0, ',', '.');
        }
        return null;
    }

    public function incrementClickCount(): void
    {
        $this->increment('click_count');
    }

    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'last_verified_at' => now(),
        ]);
    }

    public function markAsUnverified(): void
    {
        $this->update([
            'is_verified' => false,
        ]);
    }

    public function getWhatsAppUrlAttribute(): string
    {
        if ($this->platform !== 'whatsapp') {
            return $this->product_url;
        }

        // Extract phone number from WhatsApp URL if it's a wa.me link
        if (str_contains($this->product_url, 'wa.me/')) {
            return $this->product_url;
        }

        // If it's just a phone number, format it properly
        $phone = preg_replace('/[^0-9]/', '', $this->product_url);

        // Add country code if not present
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }

        $message = urlencode("Halo, saya tertarik dengan produk {$this->product->name}");
        return "https://wa.me/{$phone}?text={$message}";
    }

    public function getFinalUrlAttribute(): string
    {
        return $this->platform === 'whatsapp' ? $this->whatsapp_url : $this->product_url;
    }
}
