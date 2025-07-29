<?php

// Updated app/Filament/Resources/SmeResource.php
namespace App\Filament\Resources;

use App\Models\Sme;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use App\Models\Community;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\SmeTourismPlace;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SmeResource\Pages;

class SmeResource extends Resource
{
    protected static ?string $model = Sme::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Bisnis';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'UMKM';

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
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                return $user->getAccessibleCommunities()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('place_id')
                            ->relationship('place', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = User::find(Auth::id());
                                if ($user->isSuperAdmin()) {
                                    return \App\Models\Place::pluck('name', 'id');
                                }

                                $villageIds = $user->getAccessibleVillages()->pluck('id');
                                return \App\Models\Place::whereIn('village_id', $villageIds)->pluck('name', 'id');
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
                        Forms\Components\FileUpload::make('logo_url')
                            ->label('SME Logo')
                            ->image()
                            ->disk('public')
                            ->directory('sme/logos')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth(300)
                            ->imageResizeTargetHeight(300)
                            ->downloadable()
                            ->openable()
                            ->deletable()
                            ->previewable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Business Hours')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('business_hours.Sunday.open')
                                            ->label('Sunday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Sunday.close')
                                            ->label('Sunday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Sunday.closed')
                                            ->label('Sunday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('sunday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Monday.open')
                                            ->label('Monday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Monday.close')
                                            ->label('Monday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Monday.closed')
                                            ->label('Monday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('monday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Tuesday.open')
                                            ->label('Tuesday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Tuesday.close')
                                            ->label('Tuesday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Tuesday.closed')
                                            ->label('Tuesday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('tuesday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Wednesday.open')
                                            ->label('Wednesday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Wednesday.close')
                                            ->label('Wednesday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Wednesday.closed')
                                            ->label('Wednesday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('wednesday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Thursday.open')
                                            ->label('Thursday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Thursday.close')
                                            ->label('Thursday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Thursday.closed')
                                            ->label('Thursday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('thursday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Friday.open')
                                            ->label('Friday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Friday.close')
                                            ->label('Friday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Friday.closed')
                                            ->label('Friday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('friday_label')
                                            ->label('')
                                            ->content(''),

                                        Forms\Components\TextInput::make('business_hours.Saturday.open')
                                            ->label('Saturday Open')
                                            ->type('time')
                                            ->required()
                                            ->default('09:00'),
                                        Forms\Components\TextInput::make('business_hours.Saturday.close')
                                            ->label('Saturday Close')
                                            ->type('time')
                                            ->required()
                                            ->default('17:00'),
                                        Forms\Components\Toggle::make('business_hours.Saturday.closed')
                                            ->label('Saturday Closed')
                                            ->default(false),
                                        Forms\Components\Placeholder::make('saturday_label')
                                            ->label('')
                                            ->content(''),
                                    ])
                            ]),
                        Forms\Components\Toggle::make('is_verified')
                            ->default(false)
                            ->visible(fn() => !User::find(Auth::id())->isSmeAdmin()), // SME admins can't verify themselves
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
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
                    ->relationship('community', 'name')
                    ->options(function () {
                        $user = User::find(Auth::id());
                        return $user->getAccessibleCommunities()->pluck('name', 'id');
                    }),
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
                        Infolists\Components\TextEntry::make('business_hours')
                            ->label('Business Hours')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return 'Not set';

                                $orderedDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                $formatted = [];

                                foreach ($orderedDays as $day) {
                                    if (isset($state[$day])) {
                                        $dayData = $state[$day];
                                        if (isset($dayData['closed']) && $dayData['closed']) {
                                            $formatted[] = "{$day}: Closed";
                                        } else {
                                            $open = $dayData['open'] ?? '09:00';
                                            $close = $dayData['close'] ?? '17:00';
                                            $formatted[] = "{$day}: {$open} - {$close}";
                                        }
                                    }
                                }

                                return implode("\n", $formatted);
                            })
                            ->columnSpanFull(),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());
        return $user->getAccessibleSmes();
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
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
