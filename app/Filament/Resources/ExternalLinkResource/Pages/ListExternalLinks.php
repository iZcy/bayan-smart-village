<?php

namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use App\Models\ExternalLink;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExternalLinks extends ListRecords
{
    protected static string $resource = ExternalLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Link')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(ExternalLink::count()),

            'Social Media' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('icon', ['instagram', 'facebook', 'twitter', 'tiktok', 'youtube']))
                ->badge(ExternalLink::whereIn('icon', ['instagram', 'facebook', 'twitter', 'tiktok', 'youtube'])->count()),

            'E-commerce' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('icon', ['tokopedia', 'shopee']))
                ->badge(ExternalLink::whereIn('icon', ['tokopedia', 'shopee'])->count()),

            'Messaging' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('icon', 'whatsapp'))
                ->badge(ExternalLink::where('icon', 'whatsapp')->count()),
        ];
    }
}
