<?php

// app/Filament/Resources/MediaResource/Pages/ViewMedia.php
namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewMedia extends ViewRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Media Preview')
                    ->schema([
                        Infolists\Components\View::make('filament.media-preview')
                            ->viewData([
                                'record' => $this->getRecord()
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Media Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->prose(),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'video' => 'success',
                                'audio' => 'info',
                            }),
                        Infolists\Components\TextEntry::make('context')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'home' => 'primary',
                                'places' => 'warning',
                                'products' => 'success',
                                'articles' => 'info',
                                'gallery' => 'gray',
                                'global' => 'danger',
                            }),
                    ])->columns(2),

                Infolists\Components\Section::make('File Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('file_url')
                            ->label('File URL')
                            ->url(
                                fn($record) => $record->file_url,
                            )
                            ->openUrlInNewTab()
                            ->copyable(),
                        Infolists\Components\TextEntry::make('thumbnail_url')
                            ->label('Thumbnail URL')
                            ->url(
                                fn($record) => $record->thumbnail_url,
                            )
                            ->openUrlInNewTab()
                            ->copyable()
                            ->visible(fn($record) => $record->type === 'video' && $record->thumbnail_url),
                        Infolists\Components\TextEntry::make('formatted_duration')
                            ->label('Duration'),
                        Infolists\Components\TextEntry::make('mime_type')
                            ->label('MIME Type'),
                        Infolists\Components\TextEntry::make('formatted_file_size')
                            ->label('File Size'),
                    ])->columns(3),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name')
                            ->visible(fn($record) => $record->village_id),
                        Infolists\Components\TextEntry::make('community.name')
                            ->visible(fn($record) => $record->community_id),
                        Infolists\Components\TextEntry::make('sme.name')
                            ->visible(fn($record) => $record->sme_id),
                        Infolists\Components\TextEntry::make('place.name')
                            ->visible(fn($record) => $record->place_id),
                    ])->columns(2)
                    ->visible(fn($record) => $record->village_id || $record->community_id || $record->sme_id || $record->place_id),

                Infolists\Components\Section::make('Playback Settings')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_featured')
                            ->boolean()
                            ->label('Featured'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active'),
                        Infolists\Components\IconEntry::make('autoplay')
                            ->boolean()
                            ->label('Autoplay'),
                        Infolists\Components\IconEntry::make('loop')
                            ->boolean()
                            ->label('Loop'),
                        Infolists\Components\IconEntry::make('muted')
                            ->boolean()
                            ->label('Muted'),
                        Infolists\Components\TextEntry::make('volume')
                            ->label('Volume')
                            ->formatStateUsing(fn($state) => ($state * 100) . '%'),
                        Infolists\Components\TextEntry::make('sort_order')
                            ->label('Sort Order'),
                    ])->columns(4),

                Infolists\Components\Section::make('Additional Settings')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('settings')
                            ->label('Custom Settings')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => !empty($record->settings))
                    ->collapsible(),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Created'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime()
                            ->label('Last Updated'),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Media: {$record->title}";
    }
}
