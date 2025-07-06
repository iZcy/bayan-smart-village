<?php

namespace App\Filament\Resources\ImageResource\Pages;

use App\Filament\Resources\ImageResource;
use App\Models\Image;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListImages extends ListRecords
{
    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Upload Image')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(Image::count()),

            'With Captions' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('caption'))
                ->badge(Image::whereNotNull('caption')->count()),

            'Recent' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(Image::where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
