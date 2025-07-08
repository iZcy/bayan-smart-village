<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

// ListProducts Page
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Product')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(Product::count()),

            'Active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true))
                ->badge(Product::where('is_active', true)->count()),

            'Featured' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_featured', true))
                ->badge(Product::where('is_featured', true)->count()),

            'Available' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('availability', 'available'))
                ->badge(Product::where('availability', 'available')->count()),

            'With E-commerce' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('ecommerceLinks'))
                ->badge(Product::whereHas('ecommerceLinks')->count()),

            'SME Products' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('category', fn($q) => $q->where('type', 'sme')))
                ->badge(Product::whereHas('category', fn($q) => $q->where('type', 'sme'))->count()),

            'Tourism Products' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('category', fn($q) => $q->where('type', 'tourism')))
                ->badge(Product::whereHas('category', fn($q) => $q->where('type', 'tourism'))->count()),
        ];
    }
}
