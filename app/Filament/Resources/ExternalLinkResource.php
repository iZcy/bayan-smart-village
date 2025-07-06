<?php
// app/Filament/Resources/ExternalLinkResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Models\ExternalLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Short Links';

    protected static ?string $modelLabel = 'Short Link';

    protected static ?string $pluralModelLabel = 'Short Links';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Information')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Instagram, WhatsApp, Website')
                            ->helperText('Display name for this link')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('Full URL including https://')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->placeholder('Optional description for this link')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('icon')
                            ->options([
                                'instagram' => 'Instagram',
                                'facebook' => 'Facebook',
                                'whatsapp' => 'WhatsApp',
                                'website' => 'Website',
                                'tokopedia' => 'Tokopedia',
                                'shopee' => 'Shopee',
                                'gojek' => 'GoJek',
                                'grab' => 'Grab',
                                'youtube' => 'YouTube',
                                'tiktok' => 'TikTok',
                                'twitter' => 'Twitter',
                                'linkedin' => 'LinkedIn',
                                'telegram' => 'Telegram',
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'maps' => 'Maps/Location',
                                'link' => 'Generic Link',
                            ])
                            ->placeholder('Choose an icon')
                            ->helperText('Icon to display with the link')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->placeholder('0')
                            ->helperText('Order in which links appear (0 = first)')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive links will return 404')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->placeholder('Optional expiration date')
                            ->helperText('Link will become inactive after this date')
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Short Link Configuration')
                    ->description('Configure your short link subdomain and slug')
                    ->schema([
                        Forms\Components\TextInput::make('subdomain')
                            ->required()
                            ->placeholder('e.g., short, link, my-link')
                            ->helperText('Subdomain for the short link')
                            ->unique(
                                table: 'external_links',
                                column: 'subdomain',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, callable $get) {
                                    return $rule->where('slug', $get('slug'));
                                }
                            )
                            ->rules(['regex:/^[a-z0-9-]+$/'])
                            ->validationMessages([
                                'regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
                                'unique' => 'This subdomain and slug combination already exists.',
                            ])
                            ->live(onBlur: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate_subdomain')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (Forms\Set $set) {
                                        $set('subdomain', ExternalLink::generateRandomSubdomain());
                                    })
                            )
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->placeholder('e.g., instagram, contact, home')
                            ->helperText('Slug for the /l/ path')
                            ->rules(['regex:/^[a-z0-9_-]+$/'])
                            ->validationMessages([
                                'regex' => 'Slug can only contain lowercase letters, numbers, hyphens, and underscores.',
                            ])
                            ->live(onBlur: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate_slug')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (Forms\Set $set) {
                                        $set('slug', ExternalLink::generateRandomSlug());
                                    })
                            )
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('preview_url')
                            ->label('Generated Short URL')
                            ->content(function (Forms\Get $get) {
                                $subdomain = $get('subdomain');
                                $slug = $get('slug');
                                $domain = config('app.domain', 'kecamatanbayan.id');

                                if (!$subdomain || !$slug) {
                                    return 'Enter subdomain and slug to see preview';
                                }

                                return "https://{$subdomain}.{$domain}/l/{$slug}";
                            })
                            ->extraAttributes(['class' => 'font-mono text-sm bg-gray-50 dark:bg-gray-800 p-2 rounded'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('subdomain_url')
                    ->label('Short URL')
                    ->limit(50)
                    ->url(fn($record) => $record->subdomain_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->placeholder('Not configured')
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Short URL copied!')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('url')
                    ->label('Target URL')
                    ->searchable()
                    ->limit(40)
                    ->url(fn($record) => $record->formatted_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('icon')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->color(fn($record) => $record->expires_at && strtotime($record->expires_at) < time() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('icon')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'whatsapp' => 'WhatsApp',
                        'website' => 'Website',
                        'tokopedia' => 'Tokopedia',
                        'shopee' => 'Shopee',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All links')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Links')
                    ->query(fn($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\Action::make('visit_link')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->url(fn($record) => $record->subdomain_url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->hasSubdomainRouting() && $record->is_active),

                Tables\Actions\Action::make('copy_link')
                    ->label('Copy')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function ($record) {
                        // This would need JavaScript to actually copy
                        return redirect()->back();
                    })
                    ->visible(fn($record) => $record->hasSubdomainRouting()),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->color(Color::Orange),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Toggle Active')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('export_links')
                        ->label('Export Links')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->action(function ($records) {
                            $csv = "Label,Short URL,Target URL,Clicks,Status\n";
                            foreach ($records as $record) {
                                $status = $record->is_active ? 'Active' : 'Inactive';
                                $csv .= "\"{$record->label}\",\"{$record->subdomain_url}\",\"{$record->url}\",\"{$record->click_count}\",\"{$status}\"\n";
                            }

                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'short-links-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
}
