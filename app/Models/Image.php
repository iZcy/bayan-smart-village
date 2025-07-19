<?php

// Model: Image.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'community_id',
        'sme_id',
        'place_id',
        'image_url',
        'caption',
        'alt_text',
        'sort_order',
        'is_featured'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function sme(): BelongsTo
    {
        return $this->belongsTo(Sme::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
