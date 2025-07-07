<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalLink extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'place_id',
        'label',
        'url',
        'icon',
        'slug',
        'sort_order',
        'description',
        'click_count',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }

    // Get the subdomain from the linked village
    public function getSubdomainAttribute(): ?string
    {
        return $this->village ? $this->village->slug : null;
    }

    // Get the full subdomain URL with /l/ prefix
    public function getSubdomainUrlAttribute(): ?string
    {
        if (!$this->slug) {
            return null;
        }

        // If linked to a village, use village's domain
        if ($this->village_id && $this->village) {
            return "https://{$this->village->full_domain}/l/{$this->slug}";
        }

        // For apex domain links (no village)
        $domain = config('app.domain', 'kecamatanbayan.id');
        return "https://{$domain}/l/{$this->slug}";
    }

    // Get formatted original URL with protocol
    public function getFormattedUrlAttribute(): string
    {
        $url = $this->url;
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    // Check if this link has proper routing
    public function hasValidRouting(): bool
    {
        return !empty($this->slug);
    }

    // Check if this is an apex domain link (no village)
    public function isApexDomainLink(): bool
    {
        return is_null($this->village_id);
    }

    // Scope for links with proper routing
    public function scopeWithValidRouting($query)
    {
        return $query->whereNotNull('slug');
    }

    // Scope for ordered links
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Scope for village-specific links
    public function scopeForVillage($query, $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    // Scope for apex domain links (no village)
    public function scopeApexDomain($query)
    {
        return $query->whereNull('village_id');
    }

    // Scope for active links
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // Generate a random slug if not provided
    public static function generateRandomSlug(): string
    {
        do {
            $slug = \Illuminate\Support\Str::random(8);
        } while (self::where('slug', $slug)->exists());

        return strtolower($slug);
    }

    // Get the effective domain for this link
    public function getEffectiveDomainAttribute(): string
    {
        if ($this->village) {
            return $this->village->full_domain;
        }

        return config('app.domain', 'kecamatanbayan.id');
    }

    // Get display text for the link type
    public function getLinkTypeAttribute(): string
    {
        if ($this->village) {
            return "Village: {$this->village->name}";
        }

        return 'Apex Domain';
    }
}
