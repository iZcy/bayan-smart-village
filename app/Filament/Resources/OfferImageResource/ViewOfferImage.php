<?php

// app/Filament/Resources/OfferImageResource/Pages/ViewOfferImage.php
namespace App\Filament\Resources\OfferImageResource\Pages;

use App\Filament\Resources\OfferImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOfferImage extends ViewRecord
{
    protected static string $resource = OfferImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
