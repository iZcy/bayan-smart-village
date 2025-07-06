<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Category')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(Category::count()),

            'SME' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'sme'))
                ->badge(Category::where('type', 'sme')->count()),

            'Tourism' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'tourism'))
                ->badge(Category::where('type', 'tourism')->count()),
        ];
    }
}
