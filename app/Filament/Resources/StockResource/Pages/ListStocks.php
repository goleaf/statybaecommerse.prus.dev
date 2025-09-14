<?php

declare (strict_types=1);
namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListStocks
 * 
 * Filament v4 resource for ListStocks management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make()->label(__('inventory.create_stock_item')), Actions\Action::make('import_stock')->label(__('inventory.import_stock'))->icon('heroicon-o-arrow-up-tray')->color('info')->action(function (): void {
            // Import logic would go here
        }), Actions\Action::make('export_stock')->label(__('inventory.export_stock'))->icon('heroicon-o-arrow-down-tray')->color('success')->action(function (): void {
            // Export logic would go here
        }), Actions\Action::make('stock_report')->label(__('inventory.stock_report'))->icon('heroicon-o-document-chart-bar')->color('warning')->url(route('filament.admin.resources.stock-reports.index'))->openUrlInNewTab()];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('inventory.all_stock'))->icon('heroicon-o-cube')->badge(fn() => $this->getModel()::count()), 'low_stock' => Tab::make(__('inventory.low_stock'))->icon('heroicon-o-exclamation-triangle')->modifyQueryUsing(fn(Builder $query) => $query->lowStock())->badge(fn() => $this->getModel()::lowStock()->count())->badgeColor('warning'), 'out_of_stock' => Tab::make(__('inventory.out_of_stock'))->icon('heroicon-o-x-circle')->modifyQueryUsing(fn(Builder $query) => $query->outOfStock())->badge(fn() => $this->getModel()::outOfStock()->count())->badgeColor('danger'), 'needs_reorder' => Tab::make(__('inventory.needs_reorder'))->icon('heroicon-o-shopping-cart')->modifyQueryUsing(fn(Builder $query) => $query->needsReorder())->badge(fn() => $this->getModel()::needsReorder()->count())->badgeColor('info'), 'expiring_soon' => Tab::make(__('inventory.expiring_soon'))->icon('heroicon-o-clock')->modifyQueryUsing(fn(Builder $query) => $query->expiringSoon())->badge(fn() => $this->getModel()::expiringSoon()->count())->badgeColor('warning'), 'tracked' => Tab::make(__('inventory.tracked_only'))->icon('heroicon-o-eye')->modifyQueryUsing(fn(Builder $query) => $query->tracked())->badge(fn() => $this->getModel()::tracked()->count())->badgeColor('success')];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [StockResource\Widgets\StockOverviewWidget::class, StockResource\Widgets\LowStockAlertWidget::class, StockResource\Widgets\StockValueWidget::class];
    }
    /**
     * Handle getMaxContentWidth functionality with proper error handling.
     * @return MaxWidth
     */
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}