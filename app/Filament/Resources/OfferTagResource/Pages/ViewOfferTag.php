<?php

// app/Filament/Resources/OfferTagResource/Pages/ViewOfferTag.php
namespace App\Filament\Resources\OfferTagResource\Pages;

use App\Filament\Resources\OfferTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOfferTag extends ViewRecord
{
    protected static string $resource = OfferTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
