<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'village_id',
        'title',
        'slug',
        'content',
        'cover_image_url',
        'place_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title, $article->village_id);
            }

            // Auto-assign village_id based on place_id if not set
            if (!$article->village_id && $article->place_id) {
                $place = SmeTourismPlace::find($article->place_id);
                if ($place && $place->village_id) {
                    $article->village_id = $place->village_id;
                }
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title')) {
                $article->slug = static::generateUniqueSlug($article->title, $article->village_id, $article->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?string $villageId = null, ?string $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('village_id', $villageId)->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('village_id', $villageId)->where('slug', $slug);

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            $counter++;
        }

        return $slug;
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(SmeTourismPlace::class, 'place_id');
    }

    // Get the article URL
    public function getUrlAttribute(): string
    {
        $baseUrl = $this->village ? $this->village->url : config('app.url');
        return "{$baseUrl}/articles/{$this->slug}";
    }

    // Get reading time estimate
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, ceil($wordCount / 200)); // Assume 200 words per minute
    }

    // Get excerpt
    public function getExcerptAttribute(): string
    {
        $plainText = strip_tags($this->content ?? '');
        return Str::limit($plainText, 160);
    }
}
