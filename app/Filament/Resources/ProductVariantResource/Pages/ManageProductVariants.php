<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Pages\Support\BaseManageRecords;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Filament\Widgets\VariantAnalyticsWidget;
use App\Filament\Widgets\VariantPerformanceChart;
use App\Filament\Widgets\VariantPriceWidget;
use App\Filament\Widgets\VariantStockWidget;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

final class ManageProductVariants extends BaseManageRecords
{
    use HasWidgetTabs;

    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulk_price_update')
                ->label(__('product_variants.actions.bulk_price_update'))
                ->icon('heroicon-o-currency-euro')
                ->color('warning')
                ->action(function (): void {
                    // This will be handled by the bulk action in the table
                }),
            Actions\Action::make('export_variants')
                ->label(__('product_variants.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): void {
                    // Export functionality
                }),
            Actions\Action::make('import_variants')
                ->label(__('product_variants.actions.import'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->action(function (): void {
                    // Import functionality
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VariantAnalyticsWidget::class,
            VariantPerformanceChart::class,
            VariantStockWidget::class,
            VariantPriceWidget::class,
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('product_variants.tabs.all'))
                ->icon('heroicon-o-squares-2x2')
                ->value(fn () => ProductVariant::count()),

            'in_stock' => WidgetTab::make(__('product_variants.tabs.in_stock'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('available_quantity', '>', 0))
                ->value(fn () => ProductVariant::where('available_quantity', '>', 0)->count()),

            'low_stock' => WidgetTab::make(__('product_variants.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('available_quantity', '<=', 'low_stock_threshold')->where('track_inventory', true))
                ->value(fn () => ProductVariant::whereColumn('available_quantity', '<=', 'low_stock_threshold')->where('track_inventory', true)->count()),

            'out_of_stock' => WidgetTab::make(__('product_variants.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('available_quantity', '<=', 0)->where('track_inventory', true))
                ->value(fn () => ProductVariant::where('available_quantity', '<=', 0)->where('track_inventory', true)->count()),

            'on_sale' => WidgetTab::make(__('product_variants.tabs.on_sale'))
                ->icon('heroicon-o-tag')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_on_sale', true))
                ->value(fn () => ProductVariant::where('is_on_sale', true)->count()),

            'featured' => WidgetTab::make(__('product_variants.tabs.featured'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->value(fn () => ProductVariant::where('is_featured', true)->count()),

            'new' => WidgetTab::make(__('product_variants.tabs.new'))
                ->icon('heroicon-o-sparkles')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_new', true))
                ->value(fn () => ProductVariant::where('is_new', true)->count()),

            'bestsellers' => WidgetTab::make(__('product_variants.tabs.bestsellers'))
                ->icon('heroicon-o-trophy')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_bestseller', true))
                ->value(fn () => ProductVariant::where('is_bestseller', true)->count()),
        ];
    }
}
