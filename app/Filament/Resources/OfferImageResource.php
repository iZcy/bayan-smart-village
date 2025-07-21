<?php

// app/Filament/Resources/OfferImageResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\OfferImageResource\Pages;
use App\Models\OfferImage;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;

class OfferImageResource extends Resource
{
    protected static ?string $model = OfferImage::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationGroup = 'Business';
    protected static ?int $navigationSort = 6;
    protected static ?string $label = 'Offer Images';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Image Information')
                    ->schema([
                        Forms\Components\Select::make('offer_id')
                            ->relationship('offer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        // Updated: Use FileUpload for image
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Product Image')
                            ->image()
                            ->disk('public')
                            ->directory('products/gallery')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->imagePreviewHeight(200)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('alt_text')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_primary')
                            ->default(false),
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
                Tables\Columns\TextColumn::make('offer.name')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('alt_text')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('offer')
                    ->relationship('offer', 'name'),
                Tables\Filters\TernaryFilter::make('is_primary'),
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
                        Infolists\Components\TextEntry::make('offer.name'),
                        Infolists\Components\TextEntry::make('alt_text'),
                        Infolists\Components\TextEntry::make('sort_order'),
                        Infolists\Components\IconEntry::make('is_primary')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferImages::route('/'),
            'create' => Pages\CreateOfferImage::route('/create'),
            'view' => Pages\ViewOfferImage::route('/{record}'),
            'edit' => Pages\EditOfferImage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
