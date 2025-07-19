<?php

// Model: Community.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'slug',
        'description',
        'domain',
        'logo_url',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function smes(): HasMany
    {
        return $this->hasMany(Sme::class);
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
