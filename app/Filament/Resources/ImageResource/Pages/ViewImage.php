<?php

namespace App\Filament\Resources\ImageResource\Pages;

use App\Filament\Resources\ImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewImage extends ViewRecord
{
    protected static string $resource = ImageResource::class;

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
                Infolists\Components\Section::make('Image Details')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('')
                            ->height(400)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('place.name')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-map-pin'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('caption')
                            ->placeholder('No caption')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
