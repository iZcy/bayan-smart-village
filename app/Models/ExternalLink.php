<?php

// Model: ExternalLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalLink extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'community_id',
        'sme_id',
        'label',
        'url',
        'icon',
        'slug',
        'sort_order',
        'description',
        'click_count',
        'is_active',
        'expires_at'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
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
}
