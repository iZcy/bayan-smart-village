<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Article')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(Article::count()),

            'Linked to Places' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('place_id'))
                ->badge(Article::whereNotNull('place_id')->count()),

            'General Articles' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('place_id'))
                ->badge(Article::whereNull('place_id')->count()),

            'With Images' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('cover_image_url'))
                ->badge(Article::whereNotNull('cover_image_url')->count()),
        ];
    }
}
