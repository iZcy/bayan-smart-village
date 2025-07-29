<?php

// Model: Sme.php
namespace App\Models;

use App\Traits\HasImageUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sme extends Model
{
    use HasUuids, HasFactory, HasImageUrls;

    protected $fillable = [
        'community_id',
        'place_id',
        'name',
        'slug',
        'description',
        'type',
        'owner_name',
        'contact_phone',
        'contact_email',
        'logo_url',
        'business_hours',
        'is_verified',
        'is_active'
    ];

    protected $casts = [
        'business_hours' => 'json',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
