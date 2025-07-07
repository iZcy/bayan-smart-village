<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class VillageResource extends Resource
{
    protected static ?int $navigationSort = 0; // First in navigation

    protected static ?string $model = Village::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Villages';

    protected static ?string $modelLabel = 'Village';

    protected static ?string $pluralModelLabel = 'Villages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Village Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter village name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9-]+$/'])
                            ->validationMessages([
                                'regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
                            ])
                            ->helperText('This will be used as subdomain (e.g., village-name.kecamatanbayan.id)')
                            ->placeholder('village-name'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Describe this village...')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('domain')
                            ->placeholder('custom-domain.com')
                            ->helperText('Optional: Custom domain instead of subdomain')
                            ->url()
                            ->suffixIcon('heroicon-o-globe-alt'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive villages will not be accessible'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->placeholder('+62 xxx-xxxx-xxxx'),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->placeholder('contact@village.id'),

                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->placeholder('Full address...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location & Media')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('-8.6500000')
                            ->helperText('Decimal degrees format'),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('115.2167000')
                            ->helperText('Decimal degrees format'),

                        Forms\Components\FileUpload::make('image_url')
                            ->label('Village Image')
                            ->image()
                            ->directory('villages')
                            ->maxSize(2048)
                            ->helperText('Upload a village image (max 2MB)')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('established_at')
                            ->label('Established Date')
                            ->placeholder('When was this village established?'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Repeater::make('settings_entries')
                            ->label('Village Settings')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Setting Name')
                                    ->required()
                                    ->placeholder('e.g., population, area_km2'),

                                Forms\Components\TextInput::make('value')
                                    ->label('Value')
                                    ->required()
                                    ->placeholder('e.g., 2500, 15.5'),
                            ])
                            ->addActionLabel('Add Setting')
                            ->columns(2)
                            ->collapsed()
                            ->collapsible()
                            ->helperText('Custom settings for this village')
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // This will be handled by model mutators
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                // Convert settings array to repeater format
                                $entries = [];
                                if (isset($data['settings']) && is_array($data['settings'])) {
                                    foreach ($data['settings'] as $key => $value) {
                                        $entries[] = ['key' => $key, 'value' => (string) $value];
                                    }
                                }
                                $data['settings_entries'] = $entries;
                                return $data;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/village-placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Slug copied!')
                    ->tooltip('Click to copy'),

                Tables\Columns\TextColumn::make('full_domain')
                    ->label('Domain')
                    ->url(fn($record) => $record->url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->color('success')
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('places_count')
                    ->counts('places')
                    ->label('Places')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('external_links_count')
                    ->counts('externalLinks')
                    ->label('Links')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('established_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All villages')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('has_domain')
                    ->label('Has Custom Domain')
                    ->query(fn($query) => $query->whereNotNull('domain')),

                Tables\Filters\Filter::make('has_location')
                    ->label('Has Location Data')
                    ->query(fn($query) => $query->whereNotNull('latitude')->whereNotNull('longitude')),
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->url(fn($record) => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->is_active),

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
            'index' => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'view' => Pages\ViewVillage::route('/{record}'),
            'edit' => Pages\EditVillage::route('/{record}/edit'),
        ];
    }
}
