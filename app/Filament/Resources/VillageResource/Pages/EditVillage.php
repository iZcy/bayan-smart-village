<?php

// app/Filament/Resources/VillageResource/Pages/EditVillage.php
namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVillage extends EditRecord
{
    protected static string $resource = VillageResource::class;

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
