<?php

// app/Filament/Resources/ArticleResource/Pages/CreateArticle.php
namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

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
