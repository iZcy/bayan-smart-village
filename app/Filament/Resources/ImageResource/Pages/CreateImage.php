<?php

// app/Filament/Resources/ImageResource/Pages/CreateImage.php
namespace App\Filament\Resources\ImageResource\Pages;

use App\Filament\Resources\ImageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateImage extends CreateRecord
{
    protected static string $resource = ImageResource::class;

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
