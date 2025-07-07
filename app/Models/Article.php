<?php
// app/Models/Article.php - Updated

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

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }
}
