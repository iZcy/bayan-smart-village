<?php
// app/Models/ExternalLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExternalLink extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'label',
        'url',
        'icon',
        'subdomain',
        'slug',
        'sort_order',
        'description', // Optional description for the link
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // Get the full subdomain URL with /l/ prefix
    public function getSubdomainUrlAttribute(): ?string
    {
        if (!$this->subdomain || !$this->slug) {
            return null;
        }

        $domain = config('app.domain', 'kecamatanbayan.id');
        return "https://{$this->subdomain}.{$domain}/l/{$this->slug}";
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

    // Check if this link has subdomain routing
    public function hasSubdomainRouting(): bool
    {
        return !empty($this->subdomain) && !empty($this->slug);
    }

    // Scope for links with subdomain routing
    public function scopeWithSubdomain($query)
    {
        return $query->whereNotNull('subdomain')->whereNotNull('slug');
    }

    // Scope for ordered links
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Generate a random subdomain if not provided
    public static function generateRandomSubdomain(): string
    {
        do {
            $subdomain = 'link-' . \Illuminate\Support\Str::random(6);
        } while (self::where('subdomain', $subdomain)->exists());

        return strtolower($subdomain);
    }

    // Generate a random slug if not provided
    public static function generateRandomSlug(): string
    {
        do {
            $slug = \Illuminate\Support\Str::random(8);
        } while (self::where('slug', $slug)->exists());

        return strtolower($slug);
    }
}
