<?php

// Model: Village.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'domain',
        'latitude',
        'longitude',
        'phone_number',
        'email',
        'address',
        'image_url',
        'settings',
        'is_active',
        'established_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'established_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
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
