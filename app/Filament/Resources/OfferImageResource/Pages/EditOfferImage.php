<?php

// app/Filament/Resources/OfferImageResource/Pages/EditOfferImage.php
namespace App\Filament\Resources\OfferImageResource\Pages;

use App\Filament\Resources\OfferImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfferImage extends EditRecord
{
    protected static string $resource = OfferImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
