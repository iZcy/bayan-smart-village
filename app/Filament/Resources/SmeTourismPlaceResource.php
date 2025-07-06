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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmeTourismPlaceResource extends Resource
{
    protected static ?string $model = SmeTourismPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image_url')
                    ->image(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('custom_fields'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_url'),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmeTourismPlaces::route('/'),
            'create' => Pages\CreateSmeTourismPlace::route('/create'),
            'edit' => Pages\EditSmeTourismPlace::route('/{record}/edit'),
        ];
    }
}
