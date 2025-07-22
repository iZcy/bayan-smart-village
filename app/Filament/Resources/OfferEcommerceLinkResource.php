<?php

// app/Filament/Resources/OfferEcommerceLinkResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\OfferEcommerceLinkResource\Pages;
use App\Models\OfferEcommerceLink;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;

class OfferEcommerceLinkResource extends Resource
{
    protected static ?string $model = OfferEcommerceLink::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Develop';
    protected static ?int $navigationSort = 100;
    protected static ?string $label = 'E-commerce Links';
    protected static ?string $pluralLabel = 'E-commerce Links';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Information')
                    ->schema([
                        Forms\Components\Select::make('offer_id')
                            ->relationship('offer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(), // Disabled to prevent editing affiliation
                        Forms\Components\Select::make('platform')
                            ->options([
                                'tokopedia' => 'Tokopedia',
                                'shopee' => 'Shopee',
                                'tiktok_shop' => 'TikTok Shop',
                                'bukalapak' => 'Bukalapak',
                                'blibli' => 'Blibli',
                                'lazada' => 'Lazada',
                                'instagram' => 'Instagram',
                                'whatsapp' => 'WhatsApp',
                                'website' => 'Website',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('store_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('product_url')
                            ->required()
                            ->url(),
                        Forms\Components\TextInput::make('price_on_platform')
                            ->numeric()
                            ->prefix('IDR'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_verified')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('last_verified_at'),
                        Forms\Components\TextInput::make('click_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('offer.name')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tokopedia' => 'success',
                        'shopee' => 'warning',
                        'tiktok_shop' => 'danger',
                        'instagram' => 'purple',
                        'whatsapp' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('store_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_on_platform')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('click_count')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                        'tiktok_shop' => 'TikTok Shop',
                        'bukalapak' => 'Bukalapak',
                        'blibli' => 'Blibli',
                        'lazada' => 'Lazada',
                        'instagram' => 'Instagram',
                        'whatsapp' => 'WhatsApp',
                        'website' => 'Website',
                        'other' => 'Other',
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
                Infolists\Components\Section::make('Link Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('offer.name'),
                        Infolists\Components\TextEntry::make('platform')
                            ->badge(),
                        Infolists\Components\TextEntry::make('store_name'),
                        Infolists\Components\TextEntry::make('product_url')
                            ->url(
                                fn($record) => $record->product_url,
                            ),
                        Infolists\Components\TextEntry::make('price_on_platform')
                            ->money('IDR'),
                    ])->columns(2),

                Infolists\Components\Section::make('Statistics & Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('click_count'),
                        Infolists\Components\TextEntry::make('sort_order'),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('last_verified_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferEcommerceLinks::route('/'),
            // 'create' => Pages\CreateOfferEcommerceLink::route('/create'), // Disabled creation
            'view' => Pages\ViewOfferEcommerceLink::route('/{record}'),
            'edit' => Pages\EditOfferEcommerceLink::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
