<?php

// app/Filament/Resources/ExternalLinkResource/Pages/CreateExternalLink.php
namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExternalLink extends CreateRecord
{
    protected static string $resource = ExternalLinkResource::class;
}
