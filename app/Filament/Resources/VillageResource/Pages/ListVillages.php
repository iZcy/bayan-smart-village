<?php
// app/Filament/Resources/VillageResource/Pages/ListVillages.php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use App\Models\Village;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVillages extends ListRecords
{
    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Village')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(Village::count()),

            'Active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true))
                ->badge(Village::where('is_active', true)->count()),

            'Inactive' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', false))
                ->badge(Village::where('is_active', false)->count()),

            'With Custom Domain' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('domain'))
                ->badge(Village::whereNotNull('domain')->count()),
        ];
    }
}
