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
            Actions\Action::make('visit_subdomain')
                ->label('Visit Link')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->url(fn() => $this->record->subdomain_url)
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->hasSubdomainRouting()),

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

                        Infolists\Components\TextEntry::make('subdomain_url')
                            ->label('Subdomain URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-globe-alt')
                            ->color('success')
                            ->copyable()
                            ->copyMessage('Link copied to clipboard!')
                            ->visible(fn($record) => $record->hasSubdomainRouting()),

                        Infolists\Components\TextEntry::make('formatted_url')
                            ->label('Target URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-link')
                            ->color('info'),

                        Infolists\Components\TextEntry::make('sort_order')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-bars-3'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Subdomain Configuration')
                    ->schema([
                        Infolists\Components\TextEntry::make('subdomain')
                            ->icon('heroicon-o-globe-alt')
                            ->placeholder('No subdomain set'),

                        Infolists\Components\TextEntry::make('slug')
                            ->icon('heroicon-o-link')
                            ->placeholder('No slug set'),

                        Infolists\Components\TextEntry::make('subdomain_url')
                            ->label('Complete URL')
                            ->url(fn($state) => $state, shouldOpenInNewTab: true)
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('success')
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->hasSubdomainRouting()),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->subdomain || $record->slug),

                Infolists\Components\Section::make('QR Code')
                    ->schema([
                        Infolists\Components\View::make('filament.infolists.qr-code')
                            ->viewData(function ($record) {
                                // FIXED: Resolve closures to actual values
                                $url = $record->subdomain_url ?: $record->formatted_url;
                                $hasSubdomainRouting = $record->hasSubdomainRouting();

                                return [
                                    'url' => $url,
                                    'size' => 200,
                                    'label' => $hasSubdomainRouting ?
                                        'Subdomain Link QR Code' : 'Direct URL QR Code',
                                    'description' => $hasSubdomainRouting ?
                                        "Scan to visit {$record->subdomain}.kecamatanbayan.id/l/{$record->slug}" :
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
