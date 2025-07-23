<?php

// app/Filament/Resources/PlaceResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Place;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\PlaceResource\Pages;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());

        if ($user->isSuperAdmin()) {
            return parent::getEloquentQuery();
        }

        $villageIds = $user->getAccessibleVillages()->pluck('id');
        return parent::getEloquentQuery()->whereIn('village_id', $villageIds);
    }

    public static function canViewAny(): bool
    {
        $user = User::find(Auth::id());
        return !$user->isSmeAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = \App\Models\User::find(\Illuminate\Support\Facades\Auth::id());
                                if ($user->isSuperAdmin()) {
                                    return \App\Models\Category::pluck('name', 'id');
                                }

                                $villageIds = $user->getAccessibleVillages()->pluck('id');
                                return \App\Models\Category::whereIn('village_id', $villageIds)->pluck('name', 'id');
                            }),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4),
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Place Image')
                            ->image()
                            ->disk('public')
                            ->directory('places')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->imagePreviewHeight(150)
                            ->downloadable()
                            ->openable()
                            ->deletable()
                            ->previewable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Contact & Location')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(2),
                        Forms\Components\TextInput::make('phone_number'),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),
                    ])->columns(2),

                Forms\Components\Section::make('Custom Fields')
                    ->schema([
                        Forms\Components\KeyValue::make('custom_fields')
                            ->keyLabel('Field Name')
                            ->valueLabel('Field Value'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('smes_count')
                    ->counts('smes')
                    ->label('SMEs'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Place Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('category.name'),
                        Infolists\Components\TextEntry::make('description'),
                    ])->columns(2),

                Infolists\Components\Section::make('Contact & Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('address'),
                        Infolists\Components\TextEntry::make('phone_number'),
                        Infolists\Components\TextEntry::make('latitude'),
                        Infolists\Components\TextEntry::make('longitude'),
                    ])->columns(2),

                Infolists\Components\Section::make('Custom Fields')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('custom_fields')
                            ->hidden(fn ($record) => empty($record->custom_fields)),
                    ])
                    ->hidden(fn ($record) => empty($record->custom_fields)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'view' => Pages\ViewPlace::route('/{record}'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
