<?php

// app/Filament/Resources/OfferTagResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use App\Models\OfferTag;
use App\Models\Village;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\OfferTagResource\Pages;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OfferTagResource extends Resource
{
    protected static ?string $model = OfferTag::class;
    protected static ?string $navigationIcon = 'heroicon-o-hashtag';
    protected static ?string $navigationGroup = 'Bisnis';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Tag Penawaran';
    protected static ?string $pluralModelLabel = 'Tag Penawaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tag Information')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(table: 'offer_tags', column: 'slug', modifyRuleUsing: function ($rule, $get) {
                                return $rule->where('village_id', $get('village_id'));
                            }, ignoreRecord: true),
                        Forms\Components\TextInput::make('usage_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([10, 20, 50])
            ->columns([
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Village')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('offers_count')
                    ->counts('offers')
                    ->label('Offers'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village_id')
                    ->relationship('village', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Village'),
                Tables\Filters\Filter::make('popular')
                    ->query(fn($query) => $query->where('usage_count', '>', 0))
                    ->label('Popular Tags'),
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
            ])
            ->defaultSort('usage_count', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Tag Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name')
                            ->label('Village'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('usage_count'),
                        Infolists\Components\TextEntry::make('offers_count')
                            ->label('Total Offers'),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferTags::route('/'),
            'create' => Pages\CreateOfferTag::route('/create'),
            'view' => Pages\ViewOfferTag::route('/{record}'),
            'edit' => Pages\EditOfferTag::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        return static::getEloquentQuery()->count();
    }
}
