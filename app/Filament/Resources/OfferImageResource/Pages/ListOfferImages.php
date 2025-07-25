<?php

// app/Filament/Resources/OfferImageResource/Pages/ListOfferImages.php
namespace App\Filament\Resources\OfferImageResource\Pages;

use App\Filament\Resources\OfferImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfferImages extends ListRecords
{
    protected static string $resource = OfferImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // Disabled - creation handled via SME resource
        ];
    }
}
