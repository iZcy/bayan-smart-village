<?php

// app/Filament/Resources/SmeResource/Pages/EditSme.php
namespace App\Filament\Resources\SmeResource\Pages;

use App\Filament\Resources\SmeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSme extends EditRecord
{
    protected static string $resource = SmeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
