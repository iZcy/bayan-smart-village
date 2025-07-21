<?php

// app/Filament/Resources/MediaResource/Pages/CreateMedia.php
namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(Auth::id());

        // Auto-assign scope based on current user's role if not set
        if (!$user->isSuperAdmin()) {
            if ($user->isVillageAdmin() && empty($data['village_id'])) {
                $data['village_id'] = $user->village_id;
            } elseif ($user->isCommunityAdmin()) {
                if (empty($data['village_id'])) {
                    $data['village_id'] = $user->village_id;
                }
                if (empty($data['community_id'])) {
                    $data['community_id'] = $user->community_id;
                }
            } elseif ($user->isSmeAdmin()) {
                if (empty($data['village_id'])) {
                    $data['village_id'] = $user->village_id;
                }
                if (empty($data['community_id'])) {
                    $data['community_id'] = $user->community_id;
                }
                if (empty($data['sme_id'])) {
                    $data['sme_id'] = $user->sme_id;
                }
            }
        }

        // Set default values based on type
        if ($data['type'] === 'video' && !isset($data['muted'])) {
            $data['muted'] = true; // Videos should be muted by default for autoplay
        }

        if ($data['type'] === 'audio' && !isset($data['muted'])) {
            $data['muted'] = false; // Audio shouldn't be muted by default
        }

        // Set default volume if not provided
        if (!isset($data['volume']) || $data['volume'] === null) {
            $data['volume'] = 0.3;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

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
        return 'Create Media File';
    }
}
