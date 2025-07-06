<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'place_id',
        'label',
        'url',
        'icon',
        'sort_order'
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }
}
