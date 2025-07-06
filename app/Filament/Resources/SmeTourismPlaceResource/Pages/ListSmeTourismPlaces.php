<?php

namespace App\Filament\Resources\SmeTourismPlaceResource\Pages;

use App\Filament\Resources\SmeTourismPlaceResource;
use App\Models\SmeTourismPlace;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSmeTourismPlaces extends ListRecords
{
    protected static string $resource = SmeTourismPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Place')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(SmeTourismPlace::count()),

            'SME Places' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('category', fn($q) => $q->where('type', 'sme')))
                ->badge(SmeTourismPlace::whereHas('category', fn($q) => $q->where('type', 'sme'))->count()),

            'Tourism Places' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('category', fn($q) => $q->where('type', 'tourism')))
                ->badge(SmeTourismPlace::whereHas('category', fn($q) => $q->where('type', 'tourism'))->count()),

            'With Location' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
                ->badge(SmeTourismPlace::whereNotNull('latitude')->whereNotNull('longitude')->count()),
        ];
    }
}
