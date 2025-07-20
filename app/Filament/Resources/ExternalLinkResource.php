<?php

// app/Filament/Resources/ExternalLinkResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Models\ExternalLink;
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

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 2;

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
                    });
            });
        }

        if ($user->isCommunityAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('village_id', $user->village_id)
                    ->orWhere('community_id', $user->community_id)
                    ->orWhereHas('sme', function ($sq) use ($user) {
                        $sq->where('community_id', $user->community_id);
                    });
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
        return $form->schema([
            Forms\Components\Section::make('Link Information')
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('url')
                        ->required()
                        ->url(),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('icon')
                        ->maxLength(255)
                        ->placeholder('heroicon-o-link'),
                    Forms\Components\Textarea::make('description')
                        ->rows(3),
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
                ])->columns(3),

            Forms\Components\Section::make('Settings')
                ->schema([
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('expires_at'),
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
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->url),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sme.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('click_count')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
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
                        Infolists\Components\TextEntry::make('label'),
                        Infolists\Components\TextEntry::make('url')
                            ->url(
                                fn($record) => $record->url,
                            ),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('icon'),
                        Infolists\Components\TextEntry::make('description'),
                    ])->columns(2),

                Infolists\Components\Section::make('Associations')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('community.name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                    ])->columns(3),

                Infolists\Components\Section::make('Statistics & Settings')
                    ->schema([
                        Infolists\Components\TextEntry::make('click_count'),
                        Infolists\Components\TextEntry::make('sort_order'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('expires_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExternalLinks::route('/'),
            'create' => Pages\CreateExternalLink::route('/create'),
            'view' => Pages\ViewExternalLink::route('/{record}'),
            'edit' => Pages\EditExternalLink::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
