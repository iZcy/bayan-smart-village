<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Models\ExternalLink;
use App\Models\Village;
use App\Models\SmeTourismPlace;
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

    protected static ?string $navigationLabel = 'Short Links';

    protected static ?string $modelLabel = 'Short Link';

    protected static ?string $pluralModelLabel = 'Short Links';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Information')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Instagram, WhatsApp, Website')
                            ->helperText('Display name for this link')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('Full URL including https://')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->placeholder('Optional description for this link')
                            ->rows(2)
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

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive links will return 404')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->placeholder('Optional expiration date')
                            ->helperText('Link will become inactive after this date')
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Domain & Association')
                    ->description('Choose the domain and optionally link to specific places')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->label('Domain')
                            ->options(function () {
                                $options = ['apex' => 'Apex Domain (' . config('app.domain', 'kecamatanbayan.id') . ')'];

                                $villages = Village::active()->orderBy('name')->get();
                                foreach ($villages as $village) {
                                    $options[$village->id] = "{$village->name} ({$village->full_domain})";
                                }

                                return $options;
                            })
                            ->placeholder('Select domain type')
                            ->helperText('Choose apex domain for main site links, or village domain for village-specific links')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                // Clear place when village changes
                                $set('place_id', null);

                                // Handle apex domain selection
                                if ($state === 'apex') {
                                    $set('village_id', null);
                                }
                            })
                            ->dehydrateStateUsing(fn($state) => $state === 'apex' ? null : $state),

                        Forms\Components\Select::make('place_id')
                            ->relationship(
                                'place',
                                'name',
                                fn(Forms\Get $get) => $get('village_id') && $get('village_id') !== 'apex'
                                    ? SmeTourismPlace::where('village_id', $get('village_id'))
                                    : SmeTourismPlace::query()
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Select place (optional)')
                            ->helperText('Optionally link to a specific place')
                            ->visible(fn(Forms\Get $get) => $get('village_id') && $get('village_id') !== 'apex'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Short Link Configuration')
                    ->description('Configure your short link slug')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->placeholder('e.g., instagram, contact, home')
                            ->helperText('Slug for the /l/ path (must be unique per domain)')
                            ->rules(['regex:/^[a-z0-9_-]+$/'])
                            ->validationMessages([
                                'regex' => 'Slug can only contain lowercase letters, numbers, hyphens, and underscores.',
                            ])
                            ->live(onBlur: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate_slug')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (Forms\Set $set) {
                                        $set('slug', ExternalLink::generateRandomSlug());
                                    })
                            )
                            ->unique(
                                table: 'external_links',
                                column: 'slug',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, callable $get) {
                                    $villageId = $get('village_id');
                                    if ($villageId === 'apex') {
                                        $villageId = null;
                                    }
                                    return $rule->where('village_id', $villageId);
                                }
                            )
                            ->validationMessages([
                                'unique' => 'This slug already exists for the selected domain.',
                            ]),

                        Forms\Components\Placeholder::make('preview_url')
                            ->label('Generated Short URL')
                            ->content(function (Forms\Get $get) {
                                $slug = $get('slug');
                                $villageId = $get('village_id');

                                if (!$slug) {
                                    return 'Enter slug to see preview';
                                }

                                if ($villageId && $villageId !== 'apex') {
                                    $village = Village::find($villageId);
                                    if ($village) {
                                        return "https://{$village->full_domain}/l/{$slug}";
                                    }
                                }

                                // Apex domain
                                $domain = config('app.domain', 'kecamatanbayan.id');
                                return "https://{$domain}/l/{$slug}";
                            })
                            ->extraAttributes(['class' => 'font-mono text-sm bg-gray-50 dark:bg-gray-800 p-2 rounded'])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('link_type')
                    ->label('Domain')
                    ->badge()
                    ->color(fn($record) => $record->village ? 'primary' : 'warning')
                    ->searchable(),

                Tables\Columns\TextColumn::make('place.name')
                    ->badge()
                    ->color('secondary')
                    ->placeholder('No place')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subdomain_url')
                    ->label('Short URL')
                    ->limit(50)
                    ->url(fn($record) => $record->subdomain_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->placeholder('Not configured')
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Short URL copied!')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('url')
                    ->label('Target URL')
                    ->searchable()
                    ->limit(40)
                    ->url(fn($record) => $record->formatted_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('icon')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->color(fn($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('All domains'),

                Tables\Filters\SelectFilter::make('place')
                    ->relationship('place', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('All places'),

                Tables\Filters\SelectFilter::make('icon')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'whatsapp' => 'WhatsApp',
                        'website' => 'Website',
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All links')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Links')
                    ->query(fn($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now())),

                Tables\Filters\Filter::make('apex_domain')
                    ->label('Apex Domain Links')
                    ->query(fn($query) => $query->whereNull('village_id')),
            ])
            ->actions([
                Tables\Actions\Action::make('visit_link')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->url(fn($record) => $record->subdomain_url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->hasValidRouting() && $record->is_active),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->color(Color::Orange),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Toggle Active')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
