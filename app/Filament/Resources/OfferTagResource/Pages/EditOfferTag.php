<?php

// app/Filament/Resources/OfferTagResource/Pages/EditOfferTag.php
namespace App\Filament\Resources\OfferTagResource\Pages;

use App\Filament\Resources\OfferTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfferTag extends EditRecord
{
    protected static string $resource = OfferTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
