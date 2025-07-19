<?php

// app/Filament/Resources/ImageResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Image;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\ImageResource\Pages;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Image Information')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->required()
                            ->url(),
                        Forms\Components\TextInput::make('caption')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('alt_text')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Associations')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('sme_id')
                            ->relationship('sme', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->size(60),
                Tables\Columns\TextColumn::make('caption')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('village.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sme.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('place.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name'),
                Tables\Filters\SelectFilter::make('community')
                    ->relationship('community', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured'),
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
                Infolists\Components\Section::make('Image Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('caption'),
                        Infolists\Components\TextEntry::make('alt_text'),
                        Infolists\Components\TextEntry::make('sort_order'),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('community.name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                        Infolists\Components\TextEntry::make('place.name'),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'view' => Pages\ViewImage::route('/{record}'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
