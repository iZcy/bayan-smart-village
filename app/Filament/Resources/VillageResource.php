<?php

// app/Filament/Resources/VillageResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Models\User;
use App\Models\Village;
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

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Manajemen';
    protected static ?string $navigationLabel = 'Desa';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('Desa');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Desa');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());
        return $user->getAccessibleVillages();
    }

    public static function canViewAny(): bool
    {
        $user = User::find(Auth::id());
        return ($user->isSuperAdmin() || $user->isVillageAdmin());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Desa')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                        Forms\Components\TextInput::make('domain')
                            ->label('Domain Khusus')
                            ->url()
                            ->placeholder('contoh: namadesa.com'),
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Gambar Desa')
                            ->image()
                            ->disk('public')
                            ->directory('villages')
                            ->visibility('public')
                            ->maxSize(10240) // 10MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth(1200)
                            ->imageResizeTargetHeight(675)
                            ->downloadable()
                            ->openable()
                            ->deletable()
                            ->previewable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Nomor Telepon'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Garis Lintang')
                            ->numeric()
                            ->step(0.00000001),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Garis Bujur')
                            ->numeric()
                            ->step(0.00000001),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\DateTimePicker::make('established_at')
                            ->label('Tanggal Didirikan'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                        Forms\Components\KeyValue::make('settings')
                            ->label('Pengaturan Lainnya')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai'),
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
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Desa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('communities_count')
                    ->counts('communities')
                    ->label('Komunitas'),
                Tables\Columns\TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('established_at')
                    ->label('Didirikan')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\Filter::make('has_domain')
                    ->label('Memiliki Domain')
                    ->query(fn($query) => $query->whereNotNull('domain')),
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
                Infolists\Components\Section::make('Informasi Desa')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('Gambar'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama Desa'),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi'),
                        Infolists\Components\TextEntry::make('domain')
                            ->label('Domain')
                            ->url(
                                fn($record) => $record->domain ? url($record->domain) : null
                            ),
                    ])->columns(2),

                Infolists\Components\Section::make('Informasi Kontak')
                    ->schema([
                        Infolists\Components\TextEntry::make('phone_number')
                            ->label('Nomor Telepon'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat'),
                    ])->columns(2),

                Infolists\Components\Section::make('Lokasi & Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('latitude')
                            ->label('Garis Lintang'),
                        Infolists\Components\TextEntry::make('longitude')
                            ->label('Garis Bujur'),
                        Infolists\Components\TextEntry::make('established_at')
                            ->label('Didirikan')
                            ->dateTime(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Statistik')
                    ->schema([
                        Infolists\Components\TextEntry::make('communities_count')
                            ->label('Total Komunitas'),
                        Infolists\Components\TextEntry::make('places_count')
                            ->label('Total Tempat'),
                        Infolists\Components\TextEntry::make('categories_count')
                            ->label('Total Kategori'),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'view' => Pages\ViewVillage::route('/{record}'),
            'edit' => Pages\EditVillage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());
        return static::getEloquentQuery()->count();
    }
}
