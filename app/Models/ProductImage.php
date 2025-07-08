<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ProductImage Model
class ProductImage extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {
            // If this is set as primary, unset other primary images for the same product
            if ($image->is_primary) {
                static::where('product_id', $image->product_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($image) {
            // If this is set as primary, unset other primary images for the same product
            if ($image->is_primary && $image->isDirty('is_primary')) {
                static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
