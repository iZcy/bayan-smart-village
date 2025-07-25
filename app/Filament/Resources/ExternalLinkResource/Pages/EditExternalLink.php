<?php

// app/Filament/Resources/ExternalLinkResource/Pages/EditExternalLink.php
namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExternalLink extends EditRecord
{
    protected static string $resource = ExternalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
