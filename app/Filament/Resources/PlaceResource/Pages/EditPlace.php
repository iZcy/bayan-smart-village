<?php

// app/Filament/Resources/PlaceResource/Pages/EditPlace.php
namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
