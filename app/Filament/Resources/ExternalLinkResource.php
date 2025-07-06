<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Filament\Resources\ExternalLinkResource\RelationManagers;
use App\Models\ExternalLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('place_id')
                    ->relationship('place', 'name')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('place.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListExternalLinks::route('/'),
            'create' => Pages\CreateExternalLink::route('/create'),
            'edit' => Pages\EditExternalLink::route('/{record}/edit'),
        ];
    }
}
