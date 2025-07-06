<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    public function places(): HasMany
    {
        return $this->hasMany(SmeTourismPlace::class);
    }
}
