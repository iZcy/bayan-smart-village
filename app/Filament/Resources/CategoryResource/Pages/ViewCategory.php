<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

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
                Infolists\Components\Section::make('Category Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->icon('heroicon-o-tag'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'sme' => 'success',
                                'tourism' => 'info',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'sme' => 'SME',
                                'tourism' => 'Tourism',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Associated Places')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('places')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->icon('heroicon-o-map-pin'),
                                Infolists\Components\TextEntry::make('description')
                                    ->limit(100),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
