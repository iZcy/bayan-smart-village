<?php

namespace App\Filament\Resources\SmeTourismPlaceResource\Pages;

use App\Filament\Resources\SmeTourismPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSmeTourismPlace extends ViewRecord
{
    protected static string $resource = SmeTourismPlaceResource::class;

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
                Infolists\Components\Section::make('Place Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('Cover Image')
                            ->height(200),
                        Infolists\Components\TextEntry::make('name')
                            ->icon('heroicon-o-building-storefront'),
                        Infolists\Components\TextEntry::make('category.name')
                            ->badge()
                            ->color(fn($record) => $record->category?->type === 'sme' ? 'success' : 'info'),
                        Infolists\Components\TextEntry::make('phone_number')
                            ->icon('heroicon-o-phone')
                            ->url(fn($state) => $state ? "tel:{$state}" : null),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('latitude')
                            ->icon('heroicon-o-globe-alt'),
                        Infolists\Components\TextEntry::make('longitude')
                            ->icon('heroicon-o-globe-alt'),
                    ])
                    ->columns(2)
                    ->hidden(fn($record) => !$record->latitude || !$record->longitude),

                Infolists\Components\Section::make('Custom Properties')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('custom_fields')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn($record) => empty($record->custom_fields)),

                Infolists\Components\Section::make('Related Content')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('articles')
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->icon('heroicon-o-document-text'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->since()
                                    ->icon('heroicon-o-clock'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('externalLinks')
                            ->schema([
                                Infolists\Components\TextEntry::make('label')
                                    ->icon('heroicon-o-link'),
                                Infolists\Components\TextEntry::make('url')
                                    ->url(fn($state) => $state, shouldOpenInNewTab: true)
                                    ->limit(50),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
