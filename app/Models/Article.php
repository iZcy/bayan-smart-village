<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'title',
        'content',
        'cover_image_url',
        'place_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($article) {
            // Auto-assign village_id based on place_id if not set
            if (!$article->village_id && $article->place_id) {
                $place = SmeTourismPlace::find($article->place_id);
                if ($place && $place->village_id) {
                    $article->village_id = $place->village_id;
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
