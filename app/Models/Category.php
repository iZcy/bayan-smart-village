<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'type'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    public function places(): HasMany
    {
        return $this->hasMany(SmeTourismPlace::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    // Helper method to get category usage across places and products
    public function getTotalUsageCountAttribute(): int
    {
        return $this->places()->count() + $this->products()->count();
    }
}
