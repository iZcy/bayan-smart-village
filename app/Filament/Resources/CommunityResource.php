<?php

// Updated app/Filament/Resources/CommunityResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Village;
use Filament\Infolists;
use Filament\Forms\Form;
use App\Models\Community;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Resources\CommunityResource\Pages;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleVillages()->pluck('name', 'id');
                            })
                            ->disabled(fn() => !User::find(Auth::id())->isSuperAdmin()), // Only super admin can change village
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
                        Forms\Components\TextInput::make('domain')
                            ->url(),
                        Forms\Components\TextInput::make('logo_url')
                            ->url(),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_person'),
                        Forms\Components\TextInput::make('contact_phone'),
                        Forms\Components\TextInput::make('contact_email')
                            ->email(),
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
                Tables\Columns\TextColumn::make('village.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: User::find(Auth::id())?->isVillageAdmin()),
                Tables\Columns\TextColumn::make('smes_count')
                    ->counts('smes')
                    ->label('SMEs'),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name')
                    ->options(function () {
                        $user = User::find(Auth::id());
                        return $user->getAccessibleVillages()->pluck('name', 'id');
                    })
                    ->visible(fn() => !User::find(Auth::id())->isVillageAdmin()), // Hide for village admins since they only see their village
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
                Infolists\Components\Section::make('Community Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('logo_url'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('village.name'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('domain')
                            ->url(
                                fn($record) => $record->domain ? url($record->domain) : null
                            ),
                    ])->columns(2),

                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('contact_person'),
                        Infolists\Components\TextEntry::make('contact_phone'),
                        Infolists\Components\TextEntry::make('contact_email'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('smes_count')
                            ->label('Total SMEs'),
                        Infolists\Components\TextEntry::make('articles_count')
                            ->label('Total Articles'),
                        Infolists\Components\TextEntry::make('external_links_count')
                            ->label('Total External Links'),
                    ])->columns(3),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());
        return $user->getAccessibleCommunities();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunities::route('/'),
            'create' => Pages\CreateCommunity::route('/create'),
            'view' => Pages\ViewCommunity::route('/{record}'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
