<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use App\Models\User;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Place;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-play';
    protected static ?string $navigationGroup = 'Develop';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());

        if ($user->isSuperAdmin()) {
            return parent::getEloquentQuery();
        }

        $query = parent::getEloquentQuery();

        if ($user->isVillageAdmin()) {
            return $query->where('village_id', $user->village_id);
        }

        if ($user->isCommunityAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhere('community_id', $user->community_id);
            });
        }

        if ($user->isSmeAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhere('community_id', $user->community_id)
                    ->orWhere('sme_id', $user->sme_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Media Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Select::make('type')
                            ->options([
                                'video' => 'Video',
                                'audio' => 'Audio',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('context')
                            ->options([
                                'home' => 'Home Page',
                                'places' => 'Places Section',
                                'products' => 'Products Section',
                                'articles' => 'Articles Section',
                                'gallery' => 'Gallery Section',
                                'global' => 'Global (All Pages)',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('File Information')
                    ->schema([
                        // Updated: Use FileUpload for media files
                        Forms\Components\FileUpload::make('file_url')
                            ->label('Media File')
                            ->disk('public')
                            ->directory('media')
                            ->visibility('public')
                            ->maxSize(104857600) // 100MB
                            ->acceptedFileTypes(['video/*', 'audio/*'])
                            ->required()
                            ->columnSpanFull(),
                        // Updated: Use FileUpload for thumbnail
                        Forms\Components\FileUpload::make('thumbnail_url')
                            ->label('Thumbnail Image')
                            ->image()
                            ->disk('public')
                            ->directory('media/thumbnails')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(450)
                            ->visible(fn($get) => $get('type') === 'video')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('duration')
                            ->numeric()
                            ->suffix('seconds')
                            ->helperText('Duration in seconds')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('mime_type')
                            ->placeholder('video/mp4, audio/mpeg, etc.')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('file_size')
                            ->numeric()
                            ->suffix('bytes')
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('Associations')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleVillages()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleCommunities()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('sme_id')
                            ->relationship('sme', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleSmes()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                if ($user->isSuperAdmin()) {
                                    return Place::pluck('name', 'id');
                                }
                                $villageIds = $user->getAccessibleVillages()->pluck('id');
                                return Place::whereIn('village_id', $villageIds)->pluck('name', 'id');
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Playback Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false)
                            ->helperText('Featured media will be used as default for the context'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('autoplay')
                            ->default(false)
                            ->helperText('Auto-start playback when page loads'),
                        Forms\Components\Toggle::make('loop')
                            ->default(false)
                            ->helperText('Repeat playback continuously'),
                        Forms\Components\Toggle::make('muted')
                            ->default(true)
                            ->helperText('Start playback muted (recommended for autoplay)'),
                        Forms\Components\TextInput::make('volume')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(1)
                            ->default(0.3)
                            ->helperText('Volume level (0.0 to 1.0)'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'video' => 'success',
                        'audio' => 'info',
                    }),
                Tables\Columns\TextColumn::make('context')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'home' => 'primary',
                        'places' => 'warning',
                        'products' => 'success',
                        'articles' => 'info',
                        'gallery' => 'gray',
                        'global' => 'danger',
                    }),
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
                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Duration'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('autoplay')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'video' => 'Video',
                        'audio' => 'Audio',
                    ]),
                Tables\Filters\SelectFilter::make('context')
                    ->options([
                        'home' => 'Home Page',
                        'places' => 'Places Section',
                        'products' => 'Products Section',
                        'articles' => 'Articles Section',
                        'gallery' => 'Gallery Section',
                        'global' => 'Global (All Pages)',
                    ]),
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('autoplay'),
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
            ->defaultSort('sort_order');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Media Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge(),
                        Infolists\Components\TextEntry::make('context')
                            ->badge(),
                        Infolists\Components\TextEntry::make('file_url')
                            ->url(
                                fn($record) => $record->public_url,
                            )
                            ->openUrlInNewTab(),

                        // Media Preview Section
                        Infolists\Components\ViewEntry::make('media_preview')
                            ->label('Media Preview')
                            ->view('filament.resources.media.infolist.media-preview')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('thumbnail_url')
                            ->url(
                                fn($record) => $record->thumbnail_public_url,
                            )
                            ->openUrlInNewTab()
                            ->visible(fn($record) => $record->type === 'video'),
                    ])->columns(2),

                Infolists\Components\Section::make('File Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('formatted_duration')
                            ->label('Duration'),
                        Infolists\Components\TextEntry::make('mime_type'),
                        Infolists\Components\TextEntry::make('formatted_file_size')
                            ->label('File Size'),
                    ])->columns(3),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('community.name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                        Infolists\Components\TextEntry::make('place.name'),
                    ])->columns(2),

                Infolists\Components\Section::make('Playback Settings')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_featured')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('autoplay')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('loop')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('muted')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('volume'),
                        Infolists\Components\TextEntry::make('sort_order'),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
