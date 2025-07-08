<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

// EditProduct Page
class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

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
            ->title('Product updated')
            ->body('The product has been updated successfully.');
    }

    protected function afterSave(): void
    {
        // Update tag usage counts after saving changes
        $originalTags = $this->record->getOriginal('tags') ?? collect();
        $newTags = $this->record->tags;

        // Decrement usage for removed tags
        $originalTags->diff($newTags)->each(function ($tag) {
            $tag->decrementUsage();
        });

        // Increment usage for added tags
        $newTags->diff($originalTags)->each(function ($tag) {
            $tag->incrementUsage();
        });
    }
}
