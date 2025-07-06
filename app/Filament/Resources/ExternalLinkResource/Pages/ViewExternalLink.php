<?php

namespace App\Filament\Resources\ExternalLinkResource\Pages;

use App\Filament\Resources\ExternalLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewExternalLink extends ViewRecord
{
    protected static string $resource = ExternalLinkResource::class;

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
                Infolists\Components\Section::make('Link Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('place.name')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-map-pin'),

                        Infolists\Components\TextEntry::make('label')
                            ->icon('heroicon-o-tag'),

                        Infolists\Components\TextEntry::make('url')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('icon')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('sort_order')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-bars-3'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(2),
            ]);
    }
}
