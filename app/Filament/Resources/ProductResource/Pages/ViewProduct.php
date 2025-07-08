<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

// ViewProduct Page
class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-m-pencil-square')
                ->color(Color::Orange),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Product Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('primary_image_url')
                            ->label('Product Image')
                            ->height(300)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('village.name')
                            ->badge()
                            ->color('secondary')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('No village linked'),

                        Infolists\Components\TextEntry::make('place.name')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('No place linked'),

                        Infolists\Components\TextEntry::make('category.name')
                            ->badge()
                            ->color(fn($record) => $record->category?->type === 'sme' ? 'success' : 'info')
                            ->icon('heroicon-o-tag'),

                        Infolists\Components\TextEntry::make('display_price')
                            ->label('Price')
                            ->icon('heroicon-o-currency-dollar'),

                        Infolists\Components\TextEntry::make('availability_status')
                            ->label('Availability')
                            ->badge()
                            ->color(fn($record): string => match ($record->availability) {
                                'available' => 'success',
                                'out_of_stock' => 'danger',
                                'seasonal' => 'warning',
                                'on_demand' => 'info',
                            }),

                        Infolists\Components\TextEntry::make('view_count')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-eye'),

                        Infolists\Components\TextEntry::make('short_description')
                            ->columnSpanFull()
                            ->placeholder('No summary provided'),

                        Infolists\Components\TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Product Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('materials')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No materials specified'),

                        Infolists\Components\TextEntry::make('colors')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No colors specified'),

                        Infolists\Components\TextEntry::make('sizes')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No sizes specified'),

                        Infolists\Components\TextEntry::make('features')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No features specified'),

                        Infolists\Components\TextEntry::make('certification')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No certifications'),

                        Infolists\Components\TextEntry::make('production_time')
                            ->icon('heroicon-o-clock')
                            ->placeholder('Not specified'),

                        Infolists\Components\TextEntry::make('minimum_order')
                            ->suffix(fn($state) => $state ? ' pieces' : '')
                            ->placeholder('No minimum order'),

                        Infolists\Components\TextEntry::make('price_unit')
                            ->icon('heroicon-o-scale')
                            ->placeholder('Not specified'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('E-commerce Links')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('ecommerceLinks')
                            ->schema([
                                Infolists\Components\TextEntry::make('platform_name')
                                    ->label('Platform')
                                    ->badge()
                                    ->color(fn($record) => $record->platform_color),

                                Infolists\Components\TextEntry::make('store_name')
                                    ->label('Store')
                                    ->placeholder('No store name'),

                                Infolists\Components\TextEntry::make('formatted_price')
                                    ->label('Price')
                                    ->placeholder('No price listed'),

                                Infolists\Components\TextEntry::make('product_url')
                                    ->label('Link')
                                    ->url(fn($state) => $state, shouldOpenInNewTab: true)
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->limit(50),

                                Infolists\Components\IconEntry::make('is_verified')
                                    ->label('Verified')
                                    ->boolean(),

                                Infolists\Components\TextEntry::make('click_count')
                                    ->label('Clicks')
                                    ->badge()
                                    ->color('info'),
                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->hidden(fn($record) => $record->ecommerceLinks->isEmpty()),

                Infolists\Components\Section::make('Tags & SEO')
                    ->schema([
                        Infolists\Components\TextEntry::make('tags.name')
                            ->label('Tags')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('No tags assigned'),

                        Infolists\Components\TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-o-link'),

                        Infolists\Components\TextEntry::make('url')
                            ->label('Public URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->copyable(),

                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
