<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

// ProductTag Model
class ProductTag extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = static::generateUniqueSlug($tag->name, $tag->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('slug', $slug);

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            $counter++;
        }

        return $slug;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag_pivot', 'product_tag_id', 'product_id')
            ->withTimestamps();
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public static function findOrCreateByName(string $name): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['slug' => static::generateUniqueSlug($name)]
        );
    }
}
