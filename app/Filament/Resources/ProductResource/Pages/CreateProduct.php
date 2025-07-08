<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

// CreateProduct Page
class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Create Product')
            ->icon('heroicon-o-plus');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Create & Create Another')
            ->icon('heroicon-o-plus');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Product created')
            ->body('The product has been created successfully.');
    }

    protected function afterCreate(): void
    {
        // Update tag usage counts for newly created product
        $this->record->tags()->each(function ($tag) {
            $tag->incrementUsage();
        });
    }
}
