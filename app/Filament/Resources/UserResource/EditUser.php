<?php
// app/Filament/Resources/UserResource/Pages/EditUser.php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear unnecessary scope fields based on role
        if ($data['role'] === 'super_admin') {
            $data['village_id'] = null;
            $data['community_id'] = null;
            $data['sme_id'] = null;
        } elseif ($data['role'] === 'village_admin') {
            $data['community_id'] = null;
            $data['sme_id'] = null;
        } elseif ($data['role'] === 'community_admin') {
            $data['sme_id'] = null;
        }

        return $data;
    }
}
