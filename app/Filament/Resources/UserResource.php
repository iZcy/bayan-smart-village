<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Akun';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn($context) => $context === 'create')
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->visible(fn($context) => $context === 'create'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Access')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options(function () {
                                $user = User::find(Auth::id());
                                $roles = User::getRoles();

                                // Filter roles based on current user's permissions
                                if (!$user->isSuperAdmin()) {
                                    // Non-super admins can only create users within their scope
                                    if ($user->isVillageAdmin()) {
                                        unset($roles[User::ROLE_SUPER_ADMIN]);
                                        unset($roles[User::ROLE_VILLAGE_ADMIN]); // Can't create another village admin
                                    } elseif ($user->isCommunityAdmin()) {
                                        $roles = [
                                            User::ROLE_SME_ADMIN => $roles[User::ROLE_SME_ADMIN]
                                        ];
                                    } elseif ($user->isSmeAdmin()) {
                                        return []; // SME admins can't create users
                                    }
                                }

                                return $roles;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear scope fields when role changes
                                $set('village_id', null);
                                $set('community_id', null);
                                $set('sme_id', null);
                            }),

                        Forms\Components\Select::make('village_id')
                            ->relationship('village', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => in_array($get('role'), [
                                User::ROLE_VILLAGE_ADMIN,
                                User::ROLE_COMMUNITY_ADMIN,
                                User::ROLE_SME_ADMIN
                            ]))
                            ->required(fn($get) => in_array($get('role'), [
                                User::ROLE_VILLAGE_ADMIN,
                                User::ROLE_COMMUNITY_ADMIN,
                                User::ROLE_SME_ADMIN
                            ]))
                            ->options(function () {
                                $user = User::find(Auth::id());
                                if ($user->isSuperAdmin()) {
                                    return Village::pluck('name', 'id');
                                }
                                return $user->getAccessibleVillages()->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('community_id', null);
                                $set('sme_id', null);
                            }),

                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => in_array($get('role'), [
                                User::ROLE_COMMUNITY_ADMIN,
                                User::ROLE_SME_ADMIN
                            ]))
                            ->required(fn($get) => in_array($get('role'), [
                                User::ROLE_COMMUNITY_ADMIN,
                                User::ROLE_SME_ADMIN
                            ]))
                            ->options(function ($get) {
                                if (!$get('village_id')) {
                                    return [];
                                }
                                return Community::where('village_id', $get('village_id'))
                                    ->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('sme_id', null);
                            }),

                        Forms\Components\Select::make('sme_id')
                            ->relationship('sme', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('role') === User::ROLE_SME_ADMIN)
                            ->required(fn($get) => $get('role') === User::ROLE_SME_ADMIN)
                            ->options(function ($get) {
                                if (!$get('community_id')) {
                                    return [];
                                }
                                return Sme::where('community_id', $get('community_id'))
                                    ->pluck('name', 'id');
                            }),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        User::ROLE_SUPER_ADMIN => 'danger',
                        User::ROLE_VILLAGE_ADMIN => 'warning',
                        User::ROLE_COMMUNITY_ADMIN => 'info',
                        User::ROLE_SME_ADMIN => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => User::getRoles()[$state] ?? $state),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Village')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('Community')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sme.name')
                    ->label('SME')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(User::getRoles()),
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name'),
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
                Infolists\Components\Section::make('User Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('role')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => User::getRoles()[$state] ?? $state),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Access Scope')
                    ->schema([
                        Infolists\Components\TextEntry::make('village.name')
                            ->visible(fn($record) => $record->village_id),
                        Infolists\Components\TextEntry::make('community.name')
                            ->visible(fn($record) => $record->community_id),
                        Infolists\Components\TextEntry::make('sme.name')
                            ->visible(fn($record) => $record->sme_id),
                    ])->columns(3),

                Infolists\Components\Section::make('Account Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());

        if (false) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return parent::getEloquentQuery();
        }

        // Village admins can see community and SME admins in their village
        if ($user->isVillageAdmin()) {
            return parent::getEloquentQuery()
                ->where(function ($query) use ($user) {
                    $query->where('village_id', $user->village_id)
                        ->whereIn('role', [
                            User::ROLE_COMMUNITY_ADMIN,
                            User::ROLE_SME_ADMIN
                        ]);
                });
        }

        // Community admins can see SME admins in their community
        if ($user->isCommunityAdmin()) {
            return parent::getEloquentQuery()
                ->where('community_id', $user->community_id)
                ->where('role', User::ROLE_SME_ADMIN);
        }

        // SME admins can't see other users
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());

        if (false) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return static::getModel()::count();
        }

        return static::getEloquentQuery()->count();
    }

    public static function canCreate(): bool
    {
        $user = User::find(Auth::id());

        // SME admins can't create users
        return $user && !$user->isSmeAdmin();
    }
}
