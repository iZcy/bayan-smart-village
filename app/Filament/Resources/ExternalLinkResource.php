<?php
// app/Filament/Resources/ExternalLinkResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Models\ExternalLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Links';

    protected static ?string $modelLabel = 'Link';

    protected static ?string $pluralModelLabel = 'Links';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Information')
                    ->schema([
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a place')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Instagram, WhatsApp, Website')
                            ->helperText('Display name for this link')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('Full URL including https://')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('icon')
                            ->options([
                                'instagram' => 'Instagram',
                                'facebook' => 'Facebook',
                                'whatsapp' => 'WhatsApp',
                                'website' => 'Website',
                                'tokopedia' => 'Tokopedia',
                                'shopee' => 'Shopee',
                                'gojek' => 'GoJek',
                                'grab' => 'Grab',
                                'youtube' => 'YouTube',
                                'tiktok' => 'TikTok',
                                'twitter' => 'Twitter',
                                'linkedin' => 'LinkedIn',
                                'telegram' => 'Telegram',
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'maps' => 'Maps/Location',
                                'link' => 'Generic Link',
                            ])
                            ->placeholder('Choose an icon')
                            ->helperText('Icon to display with the link')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->placeholder('0')
                            ->helperText('Order in which links appear (0 = first)')
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Subdomain Link Configuration')
                    ->description('All links must have a subdomain and slug for the /l/ format')
                    ->schema([
                        Forms\Components\TextInput::make('subdomain')
                            ->required()
                            ->placeholder('e.g., warung-bu-sari')
                            ->helperText('Subdomain for the link (required)')
                            ->unique(
                                table: 'external_links',
                                column: 'subdomain',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, callable $get) {
                                    return $rule->where('slug', $get('slug'));
                                }
                            )
                            ->rules(['regex:/^[a-z0-9-]+$/'])
                            ->validationMessages([
                                'regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
                                'unique' => 'This subdomain and slug combination already exists.',
                            ])
                            ->live(onBlur: true)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->placeholder('e.g., contact_person, instagram')
                            ->helperText('Slug for the /l/ path (required)')
                            ->rules(['regex:/^[a-z0-9_-]+$/'])
                            ->validationMessages([
                                'regex' => 'Slug can only contain lowercase letters, numbers, hyphens, and underscores.',
                            ])
                            ->live(onBlur: true)
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('preview_url')
                            ->label('Generated URL')
                            ->content(function (Forms\Get $get) {
                                $subdomain = $get('subdomain');
                                $slug = $get('slug');
                                $domain = config('app.domain', 'kecamatanbayan.id');

                                if (!$subdomain || !$slug) {
                                    return 'Enter subdomain and slug to see preview';
                                }

                                return "https://{$subdomain}.{$domain}/l/{$slug}";
                            })
                            ->extraAttributes(['class' => 'font-mono text-sm bg-gray-50 dark:bg-gray-800 p-2 rounded'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place.name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('subdomain_url')
                    ->label('/l/ Link')
                    ->limit(50)
                    ->url(fn($record) => $record->subdomain_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->placeholder('Not configured')
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Link copied!')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('url')
                    ->label('Target URL')
                    ->searchable()
                    ->limit(40)
                    ->url(fn($record) => $record->formatted_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('icon')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('place')
                    ->relationship('place', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('icon')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'whatsapp' => 'WhatsApp',
                        'website' => 'Website',
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                    ]),

                Tables\Filters\Filter::make('configured_links')
                    ->label('Configured Links')
                    ->query(fn($query) => $query->whereNotNull('subdomain')->whereNotNull('slug'))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('visit_link')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->url(fn($record) => $record->subdomain_url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->hasSubdomainRouting()),

                // FIXED: Remove the problematic copy action
                // Tables\Actions\Action::make('copy_link')
                //     ->label('Copy')
                //     ->icon('heroicon-o-clipboard')
                //     ->color('gray')
                //     ->action(function ($record) {
                //         return $record->subdomain_url;
                //     })
                //     ->visible(fn($record) => $record->hasSubdomainRouting())
                //     ->extraAttributes([
                //         'x-on:click' => 'navigator.clipboard.writeText("' . '" + $el.dataset.url + ""); $dispatch("copied");',
                //         'x-data' => '{}',
                //         'data-url' => fn($record) => $record->subdomain_url, // This closure causes the error
                //     ]),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->color(Color::Orange),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('export_links')
                        ->label('Export Links')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->action(function ($records) {
                            $csv = "Place,Label,Subdomain URL,Target URL\n";
                            foreach ($records as $record) {
                                $csv .= "\"{$record->place->name}\",\"{$record->label}\",\"{$record->subdomain_url}\",\"{$record->url}\"\n";
                            }

                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'external-links-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExternalLinks::route('/'),
            'create' => Pages\CreateExternalLink::route('/create'),
            'view' => Pages\ViewExternalLink::route('/{record}'),
            'edit' => Pages\EditExternalLink::route('/{record}/edit'),
        ];
    }
}
