<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Village;
use App\Models\SmeTourismPlace;
use App\Models\Category;
use App\Models\ProductTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?int $navigationSort = 4;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Basic Information')
                        ->schema([
                            Forms\Components\Section::make('Product Details')
                                ->schema([
                                    Forms\Components\Select::make('village_id')
                                        ->relationship('village', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Select village (optional)')
                                        ->helperText('Choose which village this product represents')
                                        ->live()
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            // Clear place when village changes
                                            $set('place_id', null);
                                        }),

                                    Forms\Components\Select::make('place_id')
                                        ->relationship(
                                            'place',
                                            'name',
                                            fn(Forms\Get $get) => $get('village_id')
                                                ? SmeTourismPlace::where('village_id', $get('village_id'))
                                                : SmeTourismPlace::query()
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Select producer/seller (optional)')
                                        ->helperText('Link to the business/place that produces or sells this product'),

                                    Forms\Components\Select::make('category_id')
                                        ->relationship('category', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->required(),
                                            Forms\Components\Select::make('type')
                                                ->required()
                                                ->options([
                                                    'sme' => 'SME',
                                                    'tourism' => 'Tourism',
                                                ]),
                                        ]),

                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Enter product name')
                                        ->columnSpan(2),

                                    Forms\Components\Textarea::make('short_description')
                                        ->maxLength(500)
                                        ->rows(2)
                                        ->placeholder('Brief product summary (max 500 characters)')
                                        ->columnSpanFull(),

                                    Forms\Components\RichEditor::make('description')
                                        ->required()
                                        ->placeholder('Detailed product description...')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                            'h2',
                                            'h3',
                                            'blockquote',
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->columns(3),
                        ]),

                    Forms\Components\Wizard\Step::make('Pricing & Availability')
                        ->schema([
                            Forms\Components\Section::make('Pricing Information')
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('Fixed price (optional)')
                                        ->helperText('Leave empty if using price range'),

                                    Forms\Components\TextInput::make('price_unit')
                                        ->placeholder('e.g., per kg, per piece, per meter')
                                        ->helperText('Unit for the price'),

                                    Forms\Components\TextInput::make('price_range_min')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('Minimum price')
                                        ->helperText('For variable pricing'),

                                    Forms\Components\TextInput::make('price_range_max')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('Maximum price')
                                        ->helperText('For variable pricing'),

                                    Forms\Components\Select::make('availability')
                                        ->required()
                                        ->options([
                                            'available' => 'Available',
                                            'out_of_stock' => 'Out of Stock',
                                            'seasonal' => 'Seasonal',
                                            'on_demand' => 'On Demand/Pre-order',
                                        ])
                                        ->default('available'),

                                    Forms\Components\TextInput::make('minimum_order')
                                        ->numeric()
                                        ->placeholder('Minimum order quantity')
                                        ->helperText('Leave empty if no minimum'),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Product Details')
                                ->schema([
                                    Forms\Components\TagsInput::make('materials')
                                        ->placeholder('Add materials used')
                                        ->helperText('e.g., Bamboo, Cotton, Wood'),

                                    Forms\Components\TagsInput::make('colors')
                                        ->placeholder('Available colors')
                                        ->helperText('e.g., Red, Blue, Natural'),

                                    Forms\Components\TagsInput::make('sizes')
                                        ->placeholder('Available sizes')
                                        ->helperText('e.g., Small, Medium, Large'),

                                    Forms\Components\TagsInput::make('features')
                                        ->placeholder('Key features or benefits')
                                        ->helperText('e.g., Handmade, Eco-friendly, Durable'),

                                    Forms\Components\TagsInput::make('certification')
                                        ->placeholder('Certifications')
                                        ->helperText('e.g., Organic, Halal, Fair Trade'),

                                    Forms\Components\TextInput::make('production_time')
                                        ->placeholder('e.g., 2-3 days, 1 week')
                                        ->helperText('How long to produce/prepare the product'),
                                ])
                                ->columns(2),
                        ]),

                    Forms\Components\Wizard\Step::make('Media & E-commerce')
                        ->schema([
                            Forms\Components\Section::make('Product Images')
                                ->schema([
                                    Forms\Components\FileUpload::make('primary_image_url')
                                        ->label('Primary Image')
                                        ->image()
                                        ->directory('products')
                                        ->maxSize(2048)
                                        ->helperText('Main product image (max 2MB)')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Forms\Components\Section::make('E-commerce Links')
                                ->schema([
                                    Forms\Components\Repeater::make('ecommerce_links')
                                        ->relationship('ecommerceLinks')
                                        ->schema([
                                            Forms\Components\Select::make('platform')
                                                ->required()
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
                                                ->placeholder('Select platform'),

                                            Forms\Components\TextInput::make('store_name')
                                                ->placeholder('Store name on platform')
                                                ->columnSpan(2),

                                            Forms\Components\TextInput::make('product_url')
                                                ->label('Product URL')
                                                ->required()
                                                ->url()
                                                ->placeholder('https://...')
                                                ->columnSpan(3),

                                            Forms\Components\TextInput::make('price_on_platform')
                                                ->label('Price on Platform')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->placeholder('Actual selling price'),

                                            Forms\Components\Toggle::make('is_verified')
                                                ->label('Verified Link')
                                                ->default(false),

                                            Forms\Components\Toggle::make('is_active')
                                                ->label('Active')
                                                ->default(true),

                                            Forms\Components\TextInput::make('sort_order')
                                                ->label('Display Order')
                                                ->numeric()
                                                ->default(0)
                                                ->placeholder('0 = first'),
                                        ])
                                        ->columns(6)
                                        ->addActionLabel('Add E-commerce Link')
                                        ->reorderable('sort_order')
                                        ->collapsible()
                                        ->columnSpanFull(),
                                ]),

                            Forms\Components\Section::make('Settings')
                                ->schema([
                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('Featured Product')
                                        ->default(false)
                                        ->helperText('Featured products appear prominently'),

                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true)
                                        ->helperText('Inactive products are hidden from public'),

                                    Forms\Components\Select::make('product_tags')
                                        ->relationship('tags', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->required()
                                                ->placeholder('Tag name'),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return ProductTag::findOrCreateByName($data['name'])->id;
                                        })
                                        ->placeholder('Add tags for better discoverability')
                                        ->helperText('e.g., handmade, organic, traditional')
                                        ->columnSpan(2),
                                ])
                                ->columns(2),
                        ]),
                ])
                    ->columnSpan('full')
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image_url')
                    ->label('Image')
                    ->size(60)
                    ->square()
                    ->defaultImageUrl(url('/images/product-placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),

                Tables\Columns\TextColumn::make('village.name')
                    ->badge()
                    ->color('secondary')
                    ->placeholder('No village')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('place.name')
                    ->badge()
                    ->color('primary')
                    ->placeholder('No place')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color(fn($record) => $record->category?->type === 'sme' ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('display_price')
                    ->label('Price')
                    ->searchable(false)
                    ->sortable(false),

                Tables\Columns\TextColumn::make('availability')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'out_of_stock' => 'danger',
                        'seasonal' => 'warning',
                        'on_demand' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'available' => 'Available',
                        'out_of_stock' => 'Out of Stock',
                        'seasonal' => 'Seasonal',
                        'on_demand' => 'On Demand',
                    }),

                Tables\Columns\TextColumn::make('ecommerce_links_count')
                    ->counts('ecommerceLinks')
                    ->label('E-commerce')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village')
                    ->relationship('village', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('place')
                    ->relationship('place', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('availability')
                    ->options([
                        'available' => 'Available',
                        'out_of_stock' => 'Out of Stock',
                        'seasonal' => 'Seasonal',
                        'on_demand' => 'On Demand',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All products')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All products')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('has_ecommerce_links')
                    ->label('Has E-commerce Links')
                    ->query(fn($query) => $query->whereHas('ecommerceLinks')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->color(Color::Orange),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('toggle_featured')
                        ->label('Toggle Featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_featured' => !$record->is_featured]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Toggle Active')
                        ->icon('heroicon-o-eye-slash')
                        ->color('secondary')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
