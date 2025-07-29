<?php

// Model: Place.php
namespace App\Models;

use App\Traits\HasImageUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    use HasUuids, HasFactory, HasImageUrls;

    protected $fillable = [
        'village_id',
        'category_id',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone_number',
        'image_url',
        'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'json',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function smes(): HasMany
    {
        return $this->hasMany(Sme::class, 'place_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'place_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'place_id');
    }
}
