<?php

// Updated app/Filament/Resources/OfferResource.php
namespace App\Filament\Resources;

use App\Models\Sme;
use Filament\Forms;
use Filament\Tables;
use App\Models\Offer;
use Filament\Infolists;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\OfferResource\Pages;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Business';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('sme_id')
                            ->relationship('sme', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleSmes()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                if ($user->isSuperAdmin()) {
                                    return Category::pluck('name', 'id');
                                }

                                $villageIds = $user->getAccessibleVillages()->pluck('id');
                                return Category::whereIn('village_id', $villageIds)->pluck('name', 'id');
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
                            ->rows(4),
                        Forms\Components\TextInput::make('short_description')
                            ->maxLength(500),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('price_unit')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('price_range_min')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('price_range_max')
                            ->numeric()
                            ->prefix('IDR'),
                    ])->columns(2),

                Forms\Components\Section::make('Availability & Details')
                    ->schema([
                        Forms\Components\Select::make('availability')
                            ->options([
                                'available' => 'Available',
                                'out_of_stock' => 'Out of Stock',
                                'seasonal' => 'Seasonal',
                                'on_demand' => 'On Demand',
                            ])
                            ->default('available'),
                        Forms\Components\TagsInput::make('seasonal_availability')
                            ->placeholder('Add months (e.g., January, February)'),
                        Forms\Components\TextInput::make('primary_image_url')
                            ->url(),
                        Forms\Components\TextInput::make('production_time')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('minimum_order')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),

                Forms\Components\Section::make('Product Specifications')
                    ->schema([
                        Forms\Components\TagsInput::make('materials'),
                        Forms\Components\TagsInput::make('colors'),
                        Forms\Components\TagsInput::make('sizes'),
                        Forms\Components\TagsInput::make('features'),
                        Forms\Components\TagsInput::make('certification'),
                    ])->columns(2),

                Forms\Components\Section::make('Tags')
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('view_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image_url')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sme.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('availability')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'danger',
                        'seasonal' => 'warning',
                        'on_demand' => 'info',
                    }),
                Tables\Columns\TextColumn::make('tags.name')
                    ->badge()
                    ->separator(',')
                    ->limit(2),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sme')
                    ->relationship('sme', 'name')
                    ->options(function () {
                        $user = User::find(Auth::id());
                        return $user->getAccessibleSmes()->pluck('name', 'id');
                    }),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('availability')
                    ->options([
                        'available' => 'Available',
                        'out_of_stock' => 'Out of Stock',
                        'seasonal' => 'Seasonal',
                        'on_demand' => 'On Demand',
                    ]),
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_featured'),
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
                Infolists\Components\Section::make('Offer Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('primary_image_url'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                        Infolists\Components\TextEntry::make('category.name'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('short_description'),
                    ])->columns(2),

                Infolists\Components\Section::make('Pricing & Availability')
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('price_unit'),
                        Infolists\Components\TextEntry::make('availability')
                            ->badge(),
                        Infolists\Components\TextEntry::make('production_time'),
                        Infolists\Components\TextEntry::make('minimum_order'),
                        Infolists\Components\TextEntry::make('view_count'),
                    ])->columns(3),

                Infolists\Components\Section::make('Specifications')
                    ->schema([
                        Infolists\Components\TextEntry::make('materials')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('colors')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('sizes')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('features')
                            ->listWithLineBreaks(),
                    ])->columns(2),

                Infolists\Components\Section::make('Tags')
                    ->schema([
                        Infolists\Components\TextEntry::make('tags.name')
                            ->badge()
                            ->separator(','),
                    ]),

                Infolists\Components\Section::make('Related Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('ecommerceLinks_count')
                            ->label('E-commerce Links'),
                        Infolists\Components\TextEntry::make('images_count')
                            ->label('Additional Images'),
                    ])->columns(2),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());
        return $user->getAccessibleOffers();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'view' => Pages\ViewOffer::route('/{record}'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return $user->getAccessibleOffers()->count();
    }
}
