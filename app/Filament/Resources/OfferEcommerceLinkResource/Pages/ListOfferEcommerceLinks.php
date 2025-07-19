<?php

// app/Filament/Resources/OfferEcommerceLinkResource/Pages/ListOfferEcommerceLinks.php
namespace App\Filament\Resources\OfferEcommerceLinkResource\Pages;

use App\Filament\Resources\OfferEcommerceLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfferEcommerceLinks extends ListRecords
{
    protected static string $resource = OfferEcommerceLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
