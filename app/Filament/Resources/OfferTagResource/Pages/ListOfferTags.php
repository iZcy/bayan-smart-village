<?php

// app/Filament/Resources/OfferTagResource/Pages/ListOfferTags.php
namespace App\Filament\Resources\OfferTagResource\Pages;

use App\Filament\Resources\OfferTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfferTags extends ListRecords
{
    protected static string $resource = OfferTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
