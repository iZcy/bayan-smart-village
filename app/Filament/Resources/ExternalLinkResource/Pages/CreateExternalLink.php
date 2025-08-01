<?php

// app/Filament/Resources/ExternalLinkResource/Pages/CreateExternalLink.php
namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateExternalLink extends CreateRecord
{
    protected static string $resource = ExternalLinkResource::class;

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
