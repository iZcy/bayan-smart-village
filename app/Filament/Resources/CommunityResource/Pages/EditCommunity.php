<?php

// app/Filament/Resources/CommunityResource/Pages/EditCommunity.php
namespace App\Filament\Resources\CommunityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CommunityResource;

class EditCommunity extends EditRecord
{
    protected static string $resource = CommunityResource::class;

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
        
        // Handle logo_url - get raw database value instead of accessor URL
        if ($record && $record->logo_url) {
            $data['logo_url'] = $record->getRawOriginal('logo_url');
        }

        return $data;
    }
}
