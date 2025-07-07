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
            Actions\Action::make('visit_link')
                ->label('Visit Link')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->url(fn() => $this->record->subdomain_url)
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->hasValidRouting()),

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
                        Infolists\Components\TextEntry::make('label')
                            ->icon('heroicon-o-tag'),

                        Infolists\Components\TextEntry::make('icon')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('link_type')
                            ->label('Domain Type')
                            ->badge()
                            ->color(fn($record) => $record->village ? 'primary' : 'warning')
                            ->icon(fn($record) => $record->village ? 'heroicon-o-building-office-2' : 'heroicon-o-globe-alt'),

                        Infolists\Components\TextEntry::make('subdomain_url')
                            ->label('Short URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-globe-alt')
                            ->color('success')
                            ->copyable()
                            ->copyMessage('Link copied to clipboard!')
                            ->visible(fn($record) => $record->hasValidRouting()),

                        Infolists\Components\TextEntry::make('formatted_url')
                            ->label('Target URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-link')
                            ->color('info'),

                        Infolists\Components\TextEntry::make('click_count')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-cursor-arrow-ripple'),

                        Infolists\Components\TextEntry::make('sort_order')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-bars-3'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name')
                            ->label('Village')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('Apex Domain Link'),

                        Infolists\Components\TextEntry::make('place.name')
                            ->label('Place')
                            ->badge()
                            ->color('secondary')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('No specific place'),

                        Infolists\Components\TextEntry::make('effective_domain')
                            ->label('Domain')
                            ->icon('heroicon-o-globe-alt')
                            ->color('info'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('expires_at')
                            ->label('Expires At')
                            ->dateTime()
                            ->placeholder('Never expires')
                            ->color(fn($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : 'gray')
                            ->icon(fn($record) => $record->expires_at && $record->expires_at->isPast() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('QR Code')
                    ->schema([
                        Infolists\Components\View::make('filament.infolists.qr-code')
                            ->viewData(function ($record) {
                                $url = $record->subdomain_url ?: $record->formatted_url;
                                $hasValidRouting = $record->hasValidRouting();

                                return [
                                    'url' => $url,
                                    'size' => 200,
                                    'label' => $hasValidRouting ?
                                        'Short Link QR Code' : 'Direct URL QR Code',
                                    'description' => $hasValidRouting ?
                                        "Scan to visit {$record->effective_domain}/l/{$record->slug}" :
                                        "Scan to visit the direct URL",
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
