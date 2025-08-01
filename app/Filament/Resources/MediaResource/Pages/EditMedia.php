<?php

// app/Filament/Resources/MediaResource/Pages/EditMedia.php
namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        // Ensure user can only edit media within their scope
        if (!$user->isSuperAdmin()) {
            $originalRecord = $this->getRecord();

            if ($user->isVillageAdmin()) {
                $data['village_id'] = $user->village_id;
                // Clear lower scope if user changes village
                if ($data['village_id'] !== $originalRecord->village_id) {
                    $data['community_id'] = null;
                    $data['sme_id'] = null;
                    $data['place_id'] = null;
                }
            } elseif ($user->isCommunityAdmin()) {
                $data['village_id'] = $user->village_id;
                $data['community_id'] = $user->community_id;
                // Clear lower scope if user changes community
                if ($data['community_id'] !== $originalRecord->community_id) {
                    $data['sme_id'] = null;
                }
            } elseif ($user->isSmeAdmin()) {
                $data['village_id'] = $user->village_id;
                $data['community_id'] = $user->community_id;
                $data['sme_id'] = $user->sme_id;
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // If this media is set as featured, unfeatured others in the same context
        if ($data['is_featured'] ?? false) {
            $this->unfeaturedOthersInContext($record);
        }

        return $record;
    }

    private function unfeaturedOthersInContext(Model $record): void
    {
        static::getModel()::where('context', $record->context)
            ->where('type', $record->type)
            ->where('village_id', $record->village_id)
            ->where('id', '!=', $record->id)
            ->update(['is_featured' => false]);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Edit Media: {$record->title}";
    }
}
