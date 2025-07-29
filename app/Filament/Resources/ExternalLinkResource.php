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
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Tautan Eksternal';

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
                        ->live()
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
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (callable $set) {
                            // Reset all dependent fields when village changes
                            $set('community_id', null);
                            $set('sme_id', null);
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
                ])->columns(3),

            Forms\Components\Section::make('Settings')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('expires_at'),
                ])->columns(2),

            Forms\Components\Section::make('QR Code Preview')
                ->schema([
                    // QR Code Preview
                    Forms\Components\View::make('filament.infolists.qr-code')
                        ->viewData(function (callable $get) {
                            $slug = $get('slug');
                            $villageId = $get('village_id');

                            if (!$slug) {
                                return [
                                    'url' => null,
                                    'label' => 'QR Code Preview',
                                    'description' => 'Enter a slug to generate QR code',
                                    'size' => 200
                                ];
                            }

                            $baseDomain = config('app.domain', 'kecamatanbayan.id');
                            $protocol = config('smartvillage.url.protocol', 'https');

                            $url = '';
                            if ($villageId) {
                                $village = \App\Models\Village::find($villageId);
                                if ($village) {
                                    $url = "{$protocol}://{$village->slug}.{$baseDomain}/l/{$slug}";
                                }
                            } else {
                                $url = "{$protocol}://{$baseDomain}/l/{$slug}";
                            }

                            return [
                                'url' => $url,
                                'label' => 'Short Link QR Code',
                                'description' => 'Scan this QR code to access the external link',
                                'size' => 200
                            ];
                        })
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([10, 20, 50])
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

                Infolists\Components\Section::make('QR Code')
                    ->schema([
                        // QR Code Display
                        Infolists\Components\ViewEntry::make('qr_code')
                            ->label('Short Link QR Code')
                            ->view('filament.infolists.qr-code')
                            ->viewData(function ($record) {
                                $baseDomain = config('app.domain', 'kecamatanbayan.id');
                                $protocol = config('smartvillage.url.protocol', 'https');

                                $url = '';
                                if ($record->village_id && $record->village) {
                                    $url = "{$protocol}://{$record->village->slug}.{$baseDomain}/l/{$record->slug}";
                                } else {
                                    $url = "{$protocol}://{$baseDomain}/l/{$record->slug}";
                                }

                                $label = "Short Link: {$record->label}";
                                $description = "Scan this QR code to access: {$record->url}";

                                return [
                                    'url' => $url,
                                    'label' => $label,
                                    'description' => $description,
                                    'size' => 250
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
