<?php

declare (strict_types=1);
namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\VariantInventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * StockDetailsWidget
 * 
 * Filament v4 resource for StockDetailsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property VariantInventory|null $record
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class StockDetailsWidget extends BaseWidget
{
    public ?VariantInventory $record = null;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }
        $record = $this->record;
        return [Stat::make(__('inventory.current_stock'), $record->stock)->description(__('inventory.current_stock_description'))->descriptionIcon('heroicon-m-cube')->color($record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')), Stat::make(__('inventory.reserved_stock'), $record->reserved)->description(__('inventory.reserved_stock_description'))->descriptionIcon('heroicon-m-lock-closed')->color('warning'), Stat::make(__('inventory.available_stock'), $record->available_stock)->description(__('inventory.available_stock_description'))->descriptionIcon('heroicon-m-check-circle')->color($record->available_stock <= 0 ? 'danger' : ($record->available_stock <= 10 ? 'warning' : 'success')), Stat::make(__('inventory.incoming_stock'), $record->incoming)->description(__('inventory.incoming_stock_description'))->descriptionIcon('heroicon-m-arrow-down')->color('info'), Stat::make(__('inventory.stock_value'), '€' . number_format($record->stock_value, 2))->description(__('inventory.stock_value_description'))->descriptionIcon('heroicon-m-currency-euro')->color('success'), Stat::make(__('inventory.total_value'), '€' . number_format($record->total_value, 2))->description(__('inventory.total_value_description'))->descriptionIcon('heroicon-m-banknotes')->color('primary')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 3;
    }
}