<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListProducts extends BaseListRecords
{
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        if (! ProductResource::canCreate()) {
            return [];
        }

        return [
            Actions\CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('products', 'create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductResource\Widgets\ProductStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductResource\Widgets\ProductChartWidget::class,
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('products.tabs.all'))
                ->icon('heroicon-o-cube')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'published' => WidgetTab::make(__('products.tabs.published'))
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_visible', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_visible', true)->count()),
            'draft' => WidgetTab::make(__('products.tabs.draft'))
                ->icon('heroicon-o-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_visible', false))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_visible', false)->count()),
            'featured' => WidgetTab::make(__('products.tabs.featured'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),
            'low_stock' => WidgetTab::make(__('products.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('stock_quantity <= low_stock_threshold'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereRaw('stock_quantity <= low_stock_threshold')->count()),
            'out_of_stock' => WidgetTab::make(__('products.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock_quantity', '<=', 0))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('stock_quantity', '<=', 0)->count()),
        ];
    }
}
