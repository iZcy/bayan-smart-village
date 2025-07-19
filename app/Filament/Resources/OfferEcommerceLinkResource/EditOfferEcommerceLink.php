<?php

// app/Filament/Resources/OfferEcommerceLinkResource/Pages/EditOfferEcommerceLink.php
namespace App\Filament\Resources\OfferEcommerceLinkResource\Pages;

use App\Filament\Resources\OfferEcommerceLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfferEcommerceLink extends EditRecord
{
    protected static string $resource = OfferEcommerceLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
