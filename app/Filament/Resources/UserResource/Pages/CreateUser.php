<?php
// app/Filament/Resources/UserResource/Pages/CreateUser.php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Auto-assign scope based on current user's role
        if (!$user->isSuperAdmin()) {
            if ($user->isVillageAdmin()) {
                $data['village_id'] = $user->village_id;
            } elseif ($user->isCommunityAdmin()) {
                $data['village_id'] = $user->village_id;
                $data['community_id'] = $user->community_id;
            }
        }

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

    protected function handleRecordCreation(array $data): Model
    {
        $data['email_verified_at'] = now();
        return static::getModel()::create($data);
    }
}
