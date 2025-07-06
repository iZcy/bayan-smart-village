<?php

namespace App\Filament\Resources\SmeTourismPlaceResource\Pages;

use App\Filament\Resources\SmeTourismPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmeTourismPlaces extends ListRecords
{
    protected static string $resource = SmeTourismPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
