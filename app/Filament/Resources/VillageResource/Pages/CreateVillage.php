<?php
// app/Filament/Resources/VillageResource/Pages/CreateVillage.php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateVillage extends CreateRecord
{
    protected static string $resource = VillageResource::class;

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Create Village')
            ->icon('heroicon-o-plus');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Village created')
            ->body('The village has been created successfully.');
    }
}
