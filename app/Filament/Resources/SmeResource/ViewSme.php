<?php

// app/Filament/Resources/SmeResource/Pages/ViewSme.php
namespace App\Filament\Resources\SmeResource\Pages;

use App\Filament\Resources\SmeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSme extends ViewRecord
{
    protected static string $resource = SmeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
