<?php

// app/Filament/Resources/VillageResource/Pages/CreateVillage.php
namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVillage extends CreateRecord
{
    protected static string $resource = VillageResource::class;
}
