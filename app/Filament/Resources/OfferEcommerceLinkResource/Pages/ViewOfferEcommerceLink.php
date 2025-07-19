<?php

// app/Filament/Resources/OfferEcommerceLinkResource/Pages/ViewOfferEcommerceLink.php
namespace App\Filament\Resources\OfferEcommerceLinkResource\Pages;

use App\Filament\Resources\OfferEcommerceLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOfferEcommerceLink extends ViewRecord
{
    protected static string $resource = OfferEcommerceLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
