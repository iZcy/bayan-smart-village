<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmeTourismPlaceResource\Pages;
use App\Filament\Resources\SmeTourismPlaceResource\RelationManagers;
use App\Models\SmeTourismPlace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmeTourismPlaceResource extends Resource
{
    protected static ?int $navigationSort = 1;

    protected static ?string $model = SmeTourismPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Places';

    protected static ?string $modelLabel = 'Place';

    protected static ?string $pluralModelLabel = 'Places';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Basic Information')
                        ->schema([
                            Forms\Components\Section::make('Place Details')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Enter place name')
                                        ->columnSpan(2),

                                    Forms\Components\Select::make('category_id')
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->required(),
                                            Forms\Components\Select::make('type')
                                                ->required()
                                                ->options([
                                                    'sme' => 'SME',
                                                    'tourism' => 'Tourism',
                                                ]),
                                        ]),

                                    Forms\Components\TextInput::make('phone_number')
                                        ->tel()
                                        ->placeholder('+62 xxx-xxxx-xxxx'),

                                    Forms\Components\Textarea::make('description')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder('Describe this place...')
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('address')
                                        ->rows(3)
                                        ->placeholder('Full address...')
                                        ->columnSpanFull(),
                                ])
                                ->columns(3),
                        ]),

                    Forms\Components\Wizard\Step::make('Location & Media')
                        ->schema([
                            Forms\Components\Section::make('Geographic Location')
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
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Media')
                                ->schema([
                                    Forms\Components\FileUpload::make('image_url')
                                        ->label('Cover Image')
                                        ->image()
                                        ->directory('places')
                                        ->maxSize(2048)
                                        ->helperText('Upload a cover image (max 2MB)')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Custom Properties')
                        ->schema([
                            Forms\Components\Section::make('Additional Information')
                                ->schema([
                                    Forms\Components\KeyValue::make('custom_fields')
                                        ->label('Custom Properties')
                                        ->keyLabel('Property Name')
                                        ->valueLabel('Value')
                                        ->addActionLabel('Add Property')
                                        ->helperText('Add custom properties like opening hours, facilities, etc.')
                                        ->default([])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                    ->columnSpan('full')
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color(fn($record) => $record->category?->type === 'sme' ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('has_location')
                    ->label('Location')
                    ->boolean()
                    ->getStateUsing(fn($record) => !is_null($record->latitude) && !is_null($record->longitude))
                    ->trueIcon('heroicon-o-map-pin')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label('Articles')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('external_links_count')
                    ->counts('externalLinks')
                    ->label('Links')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('has_location')
                    ->label('Has Location Data')
                    ->query(fn($query) => $query->whereNotNull('latitude')->whereNotNull('longitude')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->color(Color::Orange),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmeTourismPlaces::route('/'),
            'create' => Pages\CreateSmeTourismPlace::route('/create'),
            'view' => Pages\ViewSmeTourismPlace::route('/{record}'),
            'edit' => Pages\EditSmeTourismPlace::route('/{record}/edit'),
        ];
    }
}
