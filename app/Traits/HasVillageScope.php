<?php

// app/Traits/HasVillageScope.php
namespace App\Traits;

use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

trait HasVillageScope
{
    /**
     * Scope query to specific village
     */
    public function scopeForVillage(Builder $query, Village $village): Builder
    {
        return $query->where('village_id', $village->id);
    }

    /**
     * Scope query to active village content
     */
    public function scopeActiveVillage(Builder $query): Builder
    {
        return $query->whereHas('village', function ($q) {
            $q->where('is_active', true);
        });
    }
}
