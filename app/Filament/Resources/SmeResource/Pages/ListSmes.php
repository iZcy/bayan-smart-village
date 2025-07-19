<?php

// app/Filament/Resources/SmeResource/Pages/ListSmes.php
namespace App\Filament\Resources\SmeResource\Pages;

use App\Filament\Resources\SmeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmes extends ListRecords
{
    protected static string $resource = SmeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
