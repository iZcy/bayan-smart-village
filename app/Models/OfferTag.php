<?php

// Model: OfferTag.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasVillageScope;

class OfferTag extends Model
{
    use HasUuids, HasFactory, HasVillageScope;

    protected $fillable = [
        'village_id',
        'name',
        'slug',
        'usage_count'
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_tag_pivot', 'offer_tag_id', 'offer_id');
    }
}
