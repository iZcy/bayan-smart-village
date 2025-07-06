<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmeTourismPlace extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone_number',
        'image_url',
        'category_id',
        'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'place_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'place_id');
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class, 'place_id')->orderBy('sort_order');
    }
}
