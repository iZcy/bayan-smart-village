<?php

// Resource: SmeResource.php
namespace App\Filament\Resources;

use App\Models\Sme;
use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use App\Models\Community;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\SmeTourismPlace;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\SmeResource\Pages;

class SmeResource extends Resource
{
    protected static ?string $model = Sme::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Business';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
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
                            ->rows(3),
                        Forms\Components\Select::make('type')
                            ->options([
                                'service' => 'Service',
                                'product' => 'Product',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Business Details')
                    ->schema([
                        Forms\Components\TextInput::make('owner_name'),
                        Forms\Components\TextInput::make('contact_phone'),
                        Forms\Components\TextInput::make('contact_email')
                            ->email(),
                        Forms\Components\TextInput::make('logo_url')
                            ->url(),
                        Forms\Components\KeyValue::make('business_hours')
                            ->keyLabel('Day')
                            ->valueLabel('Hours'),
                        Forms\Components\Toggle::make('is_verified')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'service' => 'info',
                        'product' => 'success',
                    }),
                Tables\Columns\TextColumn::make('offers_count')
                    ->counts('offers')
                    ->label('Offers'),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('community')
                    ->relationship('community', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'service' => 'Service',
                        'product' => 'Product',
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified'),
                Tables\Filters\TernaryFilter::make('is_active'),
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
                Infolists\Components\Section::make('SME Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('logo_url'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('community.name'),
                        Infolists\Components\TextEntry::make('place.name'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge(),
                        Infolists\Components\TextEntry::make('description'),
                    ])->columns(2),

                Infolists\Components\Section::make('Contact & Business')
                    ->schema([
                        Infolists\Components\TextEntry::make('owner_name'),
                        Infolists\Components\TextEntry::make('contact_phone'),
                        Infolists\Components\TextEntry::make('contact_email'),
                        Infolists\Components\KeyValueEntry::make('business_hours'),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmes::route('/'),
            'create' => Pages\CreateSme::route('/create'),
            'view' => Pages\ViewSme::route('/{record}'),
            'edit' => Pages\EditSme::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
