<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListSeoData extends ListRecords
{
    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('seo_data.tabs.all')),
            
            'active' => Tab::make(__('seo_data.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'pages' => Tab::make(__('seo_data.tabs.pages'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'page'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'page')->count()),
            
            'products' => Tab::make(__('seo_data.tabs.products'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),
            
            'categories' => Tab::make(__('seo_data.tabs.categories'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'category'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'category')->count()),
            
            'news' => Tab::make(__('seo_data.tabs.news'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'news'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'news')->count()),
            
            'posts' => Tab::make(__('seo_data.tabs.posts'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'post'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'post')->count()),
            
            'indexed' => Tab::make(__('seo_data.tabs.indexed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_indexed', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_indexed', true)->count()),
            
            'canonical' => Tab::make(__('seo_data.tabs.canonical'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_canonical', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_canonical', true)->count()),
        ];
    }
}
