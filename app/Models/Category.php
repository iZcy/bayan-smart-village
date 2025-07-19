<?php

// Model: Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'type',
        'description',
        'icon'
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
