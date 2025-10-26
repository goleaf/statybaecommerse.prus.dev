<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SeoDataResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListSeoData extends BaseListRecords
{
    use HasWidgetTabs;
    use SpatieTranslatableListRecords; // Track the active locale for listing translated records.

    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('seo_data.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),

            'active' => WidgetTab::make(__('seo_data.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'pages' => WidgetTab::make(__('seo_data.tabs.pages'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'page'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'page')->count()),

            'products' => WidgetTab::make(__('seo_data.tabs.products'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),

            'categories' => WidgetTab::make(__('seo_data.tabs.categories'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'category'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'category')->count()),

            'news' => WidgetTab::make(__('seo_data.tabs.news'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'news'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'news')->count()),

            'posts' => WidgetTab::make(__('seo_data.tabs.posts'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'post'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'post')->count()),

            'indexed' => WidgetTab::make(__('seo_data.tabs.indexed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_indexed', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_indexed', true)->count()),

            'canonical' => WidgetTab::make(__('seo_data.tabs.canonical'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_canonical', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_canonical', true)->count()),
        ];
    }
}
