<?php

namespace App\Filament\Resources\SmeTourismPlaceResource\Pages;

use App\Filament\Resources\SmeTourismPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSmeTourismPlace extends EditRecord
{
    protected static string $resource = SmeTourismPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Place updated')
            ->body('The place has been updated successfully.');
    }
}
