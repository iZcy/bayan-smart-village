<?php

// app/Filament/Resources/ArticleResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Artikel';
    protected static ?string $pluralModelLabel = 'Artikel';

    // ADD THIS METHOD - Filter articles by user scope
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

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

        return $query->whereRaw('1 = 0'); // No access by default
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('cover_image_url')
                            ->label('Cover Image')
                            ->image()
                            ->disk('public')
                            ->directory('articles')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(450)
                            ->downloadable()
                            ->openable()
                            ->deletable()
                            ->previewable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->columnSpanFull(),
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
                                $set('place_id', null);
                                $set('sme_id', null);
                            })
                            ->options(function () {
                                $user = Auth::user();
                                return $user->getAccessibleVillages()->pluck('name', 'id');
                            })
                            ->default(function () {
                                $user = Auth::user();
                                return !$user->isSuperAdmin() && $user->village_id ? $user->village_id : null;
                            })
                            ->disabled(fn() => !Auth::user()->isSuperAdmin()), // Only super admin can change village

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
                            ->disabled(fn(callable $get): bool => !$get('village_id')),

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
                            ->disabled(fn(callable $get): bool => !$get('village_id')),

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
                            ->disabled(fn(callable $get): bool => !$get('community_id')),
                    ])->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                        Forms\Components\Toggle::make('is_published')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->default(now()),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([10, 20, 50])
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_url')
                    ->label('Cover')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
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
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\TernaryFilter::make('is_published'),
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
                Infolists\Components\Section::make('Article Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('cover_image_url'),
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('content')
                            ->html()
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('community.name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                        Infolists\Components\TextEntry::make('place.name'),
                    ])->columns(2),

                Infolists\Components\Section::make('Publishing Status')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_featured')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_published')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('published_at')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'view' => Pages\ViewArticle::route('/{record}'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        return static::getEloquentQuery()->count();
    }
}
