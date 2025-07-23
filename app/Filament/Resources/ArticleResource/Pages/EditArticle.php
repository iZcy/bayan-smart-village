<?php

// app/Filament/Resources/ArticleResource/Pages/EditArticle.php
namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

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
        
        // Handle cover image - get raw database value instead of accessor URL
        if ($record && $record->cover_image_url) {
            $data['cover_image_url'] = $record->getRawOriginal('cover_image_url');
        }

        return $data;
    }
}
