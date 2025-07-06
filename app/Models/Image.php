<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasUuids;

    protected $fillable = [
        'place_id',
        'image_url',
        'caption'
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }
}
