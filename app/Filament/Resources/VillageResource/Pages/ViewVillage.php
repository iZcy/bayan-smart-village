<?php
// app/Filament/Resources/VillageResource/Pages/ViewVillage.php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewVillage extends ViewRecord
{
    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('visit')
                ->label('Visit Village')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->url(fn() => $this->record->url)
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->is_active),

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
                Infolists\Components\Section::make('Village Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('Village Image')
                            ->height(200),

                        Infolists\Components\TextEntry::make('name')
                            ->icon('heroicon-o-building-office-2'),

                        Infolists\Components\TextEntry::make('slug')
                            ->badge()
                            ->color('primary')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('full_domain')
                            ->label('Domain')
                            ->url(fn($record) => $record->url, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-globe-alt')
                            ->color('success'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('established_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('phone_number')
                            ->icon('heroicon-o-phone')
                            ->url(fn($state) => $state ? "tel:{$state}" : null),

                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->url(fn($state) => $state ? "mailto:{$state}" : null),

                        Infolists\Components\TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('latitude')
                            ->icon('heroicon-o-globe-alt'),

                        Infolists\Components\TextEntry::make('longitude')
                            ->icon('heroicon-o-globe-alt'),
                    ])
                    ->columns(2)
                    ->hidden(fn($record) => !$record->latitude || !$record->longitude),

                Infolists\Components\Section::make('Settings')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('settings')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn($record) => empty($record->settings)),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('places_count')
                            ->label('Places')
                            ->state(fn($record) => $record->places()->count())
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('articles_count')
                            ->label('Articles')
                            ->state(fn($record) => $record->articles()->count())
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('external_links_count')
                            ->label('External Links')
                            ->state(fn($record) => $record->externalLinks()->count())
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('images_count')
                            ->label('Images')
                            ->state(fn($record) => $record->images()->count())
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(4),
            ]);
    }
}
