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

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 3;

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
                        Forms\Components\TextInput::make('image_url')
                            ->url(),
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
                        Infolists\Components\KeyValueEntry::make('custom_fields'),
                    ]),
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
        return static::getModel()::count();
    }
}
