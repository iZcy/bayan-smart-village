<?php
// app/Models/Image.php - Updated

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

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }
}
