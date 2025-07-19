<?php


// app/Traits/HasSlug.php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait
     */
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = $model->generateSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = $model->generateSlug($model->name);
            }
        });
    }

    /**
     * Generate a unique slug
     */
    protected function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $model = static::class;

        // Check for scope-based uniqueness
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists (override in models for scoped uniqueness)
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }

    /**
     * Get route key name
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
