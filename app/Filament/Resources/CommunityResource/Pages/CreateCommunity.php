<?php

// app/Filament/Resources/CommunityResource/Pages/CreateCommunity.php
namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCommunity extends CreateRecord
{
    protected static string $resource = CommunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Auto-assign village for non-super-admin users
        if (!$user->isSuperAdmin() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }

        return $data;
    }
}
