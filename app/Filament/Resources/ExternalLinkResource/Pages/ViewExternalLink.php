<?php

// app/Filament/Resources/ExternalLinkResource/Pages/ViewExternalLink.php
namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExternalLink extends ViewRecord
{
    protected static string $resource = ExternalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
