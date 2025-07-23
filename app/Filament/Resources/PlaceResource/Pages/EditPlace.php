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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        
        // Handle image_url - get raw database value instead of accessor URL
        if ($record && $record->image_url) {
            $data['image_url'] = $record->getRawOriginal('image_url');
        }

        return $data;
    }
}
