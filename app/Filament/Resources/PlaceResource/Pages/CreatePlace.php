<?php

// app/Filament/Resources/PlaceResource/Pages/CreatePlace.php
namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePlace extends CreateRecord
{
    protected static string $resource = PlaceResource::class;

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
