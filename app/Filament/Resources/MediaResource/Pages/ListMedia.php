<?php

// app/Filament/Resources/MediaResource/Pages/ListMedia.php
namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any widgets here if needed
        ];
    }

    public function getTitle(): string
    {
        return 'Media Files';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
