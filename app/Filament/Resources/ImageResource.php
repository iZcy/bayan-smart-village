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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());

        if ($user->isSuperAdmin()) {
            return parent::getEloquentQuery();
        }

        $query = parent::getEloquentQuery();

        if ($user->isVillageAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhereHas('community', function ($sq) use ($user) {
                        $sq->where('village_id', $user->village_id);
                    })
                    ->orWhereHas('sme.community', function ($sq) use ($user) {
                        $sq->where('village_id', $user->village_id);
                    })
                    ->orWhereHas('place', function ($sq) use ($user) {
                        $sq->where('village_id', $user->village_id);
                    });
            });
        }

        if ($user->isCommunityAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhere('community_id', $user->community_id)
                    ->orWhereHas('sme', function ($sq) use ($user) {
                        $sq->where('community_id', $user->community_id);
                    })
                    ->orWhereHas('place', function ($sq) use ($user) {
                        $sq->where('village_id', $user->village_id);
                    });
            });
        }

        if ($user->isSmeAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhere('community_id', $user->community_id)
                    ->orWhere('sme_id', $user->sme_id)
                    ->orWhereHas('place', function ($sq) use ($user) {
                        $sq->where('village_id', $user->village_id);
                    });
            });
        }

        return $query->whereRaw('1 = 0'); // No access by default
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Image Information')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Image File')
                            ->image()
                            ->disk('public')
                            ->directory('gallery')
                            ->visibility('public')
                            ->maxSize(10240) // 10MB
                            ->imagePreviewHeight(200)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('caption')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('alt_text')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Associations')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                // Reset all dependent fields when village changes
                                $set('community_id', null);
                                $set('sme_id', null);
                                $set('place_id', null);
                            })
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleVillages()->pluck('name', 'id');
                            }),
                        
                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                // Reset SME when community changes
                                $set('sme_id', null);
                            })
                            ->options(function (callable $get) {
                                $villageId = $get('village_id');
                                if (!$villageId) {
                                    return [];
                                }
                                return \App\Models\Community::where('village_id', $villageId)->pluck('name', 'id');
                            })
                            ->disabled(fn (callable $get): bool => !$get('village_id')),
                        
                        Forms\Components\Select::make('sme_id')
                            ->relationship('sme', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $communityId = $get('community_id');
                                if (!$communityId) {
                                    return [];
                                }
                                return \App\Models\Sme::where('community_id', $communityId)->pluck('name', 'id');
                            })
                            ->disabled(fn (callable $get): bool => !$get('community_id')),
                        
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $villageId = $get('village_id');
                                if (!$villageId) {
                                    return [];
                                }
                                return \App\Models\Place::where('village_id', $villageId)->pluck('name', 'id');
                            })
                            ->disabled(fn (callable $get): bool => !$get('village_id')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->size(60)
                    ->circular(false),
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
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
