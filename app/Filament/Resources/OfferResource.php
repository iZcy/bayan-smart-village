<?php

// Updated app/Filament/Resources/OfferResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferResource\Pages;
use App\Models\Category;
use App\Models\Offer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Business';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Basic Information')
                        ->description('Essential product details')
                        ->schema([
                            Forms\Components\Select::make('sme_id')
                                ->relationship('sme', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $user = User::find(Auth::id());

                                    return $user->getAccessibleSmes()->pluck('name', 'id');
                                }),
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $user = User::find(Auth::id());
                                    if ($user->isSuperAdmin()) {
                                        return Category::pluck('name', 'id');
                                    }

                                    $villageIds = $user->getAccessibleVillages()->pluck('id');

                                    return Category::whereIn('village_id', $villageIds)->pluck('name', 'id');
                                }),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('short_description')
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Pricing & Availability')
                        ->description('Set pricing and availability details')
                        ->schema([
                            Forms\Components\TextInput::make('price')
                                ->numeric()
                                ->prefix('IDR'),
                            Forms\Components\TextInput::make('price_unit')
                                ->maxLength(50),
                            Forms\Components\TextInput::make('price_range_min')
                                ->numeric()
                                ->prefix('IDR'),
                            Forms\Components\TextInput::make('price_range_max')
                                ->numeric()
                                ->prefix('IDR'),
                            Forms\Components\Select::make('availability')
                                ->options([
                                    'available' => 'Available',
                                    'out_of_stock' => 'Out of Stock',
                                    'seasonal' => 'Seasonal',
                                    'on_demand' => 'On Demand',
                                ])
                                ->default('available'),
                            Forms\Components\TagsInput::make('seasonal_availability')
                                ->placeholder('Add months (e.g., January, February)')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('production_time')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('minimum_order')
                                ->numeric()
                                ->default(1),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Images')
                        ->description('Upload primary image and additional product images')
                        ->schema([
                            Forms\Components\Section::make('Primary Image')
                                ->description('This will be the main image displayed for your product')
                                ->schema([
                                    Forms\Components\FileUpload::make('primary_image_url')
                                        ->label('Primary Product Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('products/primary')
                                        ->visibility('public')
                                        ->maxSize(5120)
                                        ->imagePreviewHeight(200)
                                        ->downloadable()
                                        ->openable()
                                        ->deletable()
                                        ->previewable()
                                        ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                                        ->dehydrateStateUsing(function ($state) {
                                            if (! $state) {
                                                return $state;
                                            }
                                            if (is_array($state)) {
                                                return $state;
                                            } // Handle multiple files case

                                            return str_starts_with($state, 'http') ? $state : config('app.url').'/storage/'.$state;
                                        })
                                        ->afterStateHydrated(function ($component, $state) {
                                            if ($state && is_string($state) && str_contains($state, '/storage/')) {
                                                $relativePath = str_replace([
                                                    'http://dev-svnova.id/storage/',
                                                    'https://dev-svnova.id/storage/',
                                                    config('app.url').'/storage/',
                                                ], '', $state);
                                                $component->state($relativePath);
                                            } elseif ($state && is_string($state) && (str_contains($state, 'picsum.photos') || str_starts_with($state, 'http'))) {
                                                // Hide external URLs
                                                $component->state(null);
                                            }
                                        })
                                        ->helperText('Upload the main image for your product. This will be shown in listings and as the primary display.')
                                        ->required(),
                                ])
                                ->collapsible(),
                            Forms\Components\Section::make('Additional Images')
                                ->description('Add more images to showcase your product from different angles')
                                ->schema([
                                    Forms\Components\Repeater::make('additionalImages')
                                        ->relationship('additionalImages')
                                        ->label('Additional Product Images')
                                        ->schema([
                                            Forms\Components\FileUpload::make('image_url')
                                                ->label('Image')
                                                ->image()
                                                ->disk('public')
                                                ->directory('products/gallery')
                                                ->visibility('public')
                                                ->maxSize(5120)
                                                ->imagePreviewHeight(150)
                                                ->downloadable()
                                                ->openable()
                                                ->deletable()
                                                ->previewable()
                                                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                                                ->dehydrateStateUsing(function ($state) {
                                                    if (! $state) {
                                                        return $state;
                                                    }
                                                    if (is_array($state)) {
                                                        return $state;
                                                    } // Handle multiple files case

                                                    return str_starts_with($state, 'http') ? $state : config('app.url').'/storage/'.$state;
                                                })
                                                ->afterStateHydrated(function ($component, $state) {
                                                    if ($state && is_string($state) && str_contains($state, '/storage/')) {
                                                        $relativePath = str_replace([
                                                            'http://dev-svnova.id/storage/',
                                                            'https://dev-svnova.id/storage/',
                                                            config('app.url').'/storage/',
                                                        ], '', $state);
                                                        $component->state($relativePath);
                                                    } elseif ($state && is_string($state) && (str_contains($state, 'picsum.photos') || str_starts_with($state, 'http'))) {
                                                        // Hide external URLs
                                                        $component->state(null);
                                                    }
                                                })
                                                ->required(),
                                            Forms\Components\TextInput::make('alt_text')
                                                ->label('Alt Text')
                                                ->maxLength(255)
                                                ->placeholder('Describe this image for accessibility'),
                                        ])
                                        ->reorderable()
                                        ->columns(2)
                                        ->collapsed()
                                        ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? 'Additional Image')
                                        ->addActionLabel('Add Another Image')
                                        ->maxItems(9)
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),

                    Forms\Components\Wizard\Step::make('Specifications')
                        ->description('Product specifications and features')
                        ->schema([
                            Forms\Components\TagsInput::make('materials')
                                ->placeholder('Add materials (e.g., Cotton, Wood, Metal)'),
                            Forms\Components\TagsInput::make('colors')
                                ->placeholder('Add colors (e.g., Red, Blue, Green)'),
                            Forms\Components\TagsInput::make('sizes')
                                ->placeholder('Add sizes (e.g., Small, Medium, Large)'),
                            Forms\Components\TagsInput::make('features')
                                ->placeholder('Add features (e.g., Waterproof, Handmade)'),
                            Forms\Components\TagsInput::make('certification')
                                ->placeholder('Add certifications (e.g., ISO, Halal)')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('E-commerce Links')
                        ->description('Add links to e-commerce platforms')
                        ->schema([
                            Forms\Components\Repeater::make('ecommerceLinks')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('platform')
                                        ->options([
                                            'tokopedia' => 'Tokopedia',
                                            'shopee' => 'Shopee',
                                            'tiktok_shop' => 'TikTok Shop',
                                            'bukalapak' => 'Bukalapak',
                                            'blibli' => 'Blibli',
                                            'lazada' => 'Lazada',
                                            'instagram' => 'Instagram',
                                            'whatsapp' => 'WhatsApp',
                                            'website' => 'Website',
                                            'other' => 'Other',
                                        ])
                                        ->required()
                                        ->live()
                                        ->disableOptionWhen(function ($value, $state, Forms\Get $get) {
                                            // Get all selected platforms in the current repeater
                                            $selectedPlatforms = collect($get('../../ecommerceLinks'))
                                                ->pluck('platform')
                                                ->filter()
                                                ->toArray();

                                            // If this is the current item, don't disable its own value
                                            $currentPlatform = $state;
                                            if ($value === $currentPlatform) {
                                                return false;
                                            }

                                            // Disable if platform is already selected elsewhere
                                            return in_array($value, $selectedPlatforms);
                                        }),
                                    Forms\Components\TextInput::make('store_name')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('product_url')
                                        ->required()
                                        ->url()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('price_on_platform')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->columnSpan(1),
                                    Forms\Components\Toggle::make('is_verified')
                                        ->default(false),
                                    Forms\Components\Toggle::make('is_active')
                                        ->default(true),
                                ])
                                ->reorderable()
                                ->columns(2)
                                ->collapsed()
                                ->itemLabel(function (array $state): ?string {
                                    $platformLabels = [
                                        'tokopedia' => 'Tokopedia',
                                        'shopee' => 'Shopee',
                                        'tiktok_shop' => 'TikTok Shop',
                                        'bukalapak' => 'Bukalapak',
                                        'blibli' => 'Blibli',
                                        'lazada' => 'Lazada',
                                        'instagram' => 'Instagram',
                                        'whatsapp' => 'WhatsApp',
                                        'website' => 'Website',
                                        'other' => 'Other',
                                    ];

                                    if (empty($state['platform'])) {
                                        return 'Select Platform';
                                    }

                                    $platform = $platformLabels[$state['platform']] ?? $state['platform'];
                                    $storeName = ! empty($state['store_name']) ? ' - '.$state['store_name'] : '';

                                    return $platform.$storeName;
                                })
                                ->live()
                                ->addActionLabel('Add E-commerce Link')
                                ->minItems(1)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Tags & Settings')
                        ->description('Add tags and configure settings')
                        ->schema([
                            Forms\Components\Select::make('tags')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                    Forms\Components\TextInput::make('slug')
                                        ->required(),
                                ])
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured Product')
                                ->helperText('Featured products appear prominently on the website')
                                ->default(false),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Active products are visible to customers')
                                ->default(true),
                        ])->columns(2),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image_url')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sme.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('availability')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'danger',
                        'seasonal' => 'warning',
                        'on_demand' => 'info',
                    }),
                Tables\Columns\TextColumn::make('tags.name')
                    ->badge()
                    ->separator(',')
                    ->limit(2),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sme')
                    ->relationship('sme', 'name')
                    ->options(function () {
                        $user = User::find(Auth::id());

                        return $user->getAccessibleSmes()->pluck('name', 'id');
                    }),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('availability')
                    ->options([
                        'available' => 'Available',
                        'out_of_stock' => 'Out of Stock',
                        'seasonal' => 'Seasonal',
                        'on_demand' => 'On Demand',
                    ]),
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_featured'),
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
                Infolists\Components\Section::make('Offer Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('primary_image_url'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('sme.name'),
                        Infolists\Components\TextEntry::make('category.name'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('short_description'),
                    ])->columns(2),

                Infolists\Components\Section::make('Pricing & Availability')
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('price_unit'),
                        Infolists\Components\TextEntry::make('availability')
                            ->badge(),
                        Infolists\Components\TextEntry::make('production_time'),
                        Infolists\Components\TextEntry::make('minimum_order'),
                        Infolists\Components\TextEntry::make('view_count'),
                    ])->columns(3),

                Infolists\Components\Section::make('Specifications')
                    ->schema([
                        Infolists\Components\TextEntry::make('materials')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('colors')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('sizes')
                            ->listWithLineBreaks(),
                        Infolists\Components\TextEntry::make('features')
                            ->listWithLineBreaks(),
                    ])->columns(2),

                Infolists\Components\Section::make('Tags')
                    ->schema([
                        Infolists\Components\TextEntry::make('tags.name')
                            ->badge()
                            ->separator(','),
                    ]),

                Infolists\Components\Section::make('Related Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('ecommerceLinks_count')
                            ->label('E-commerce Links'),
                        Infolists\Components\TextEntry::make('images_count')
                            ->label('Additional Images'),
                    ])->columns(2),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(Auth::id());

        return $user->getAccessibleOffers();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'view' => Pages\ViewOffer::route('/{record}'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = User::find(Auth::id());

        return static::getEloquentQuery()->count();
    }
}
