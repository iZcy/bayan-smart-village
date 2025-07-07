<?php

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
        'place_id',
        'image_url',
        'caption'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($image) {
            // Auto-assign village_id based on place_id if not set
            if (!$image->village_id && $image->place_id) {
                $place = SmeTourismPlace::find($image->place_id);
                if ($place && $place->village_id) {
                    $image->village_id = $place->village_id;
                }
            }
        });
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }
}
