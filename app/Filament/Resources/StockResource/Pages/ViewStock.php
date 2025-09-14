<?php

declare (strict_types=1);
namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
/**
 * ViewStock
 * 
 * Filament v4 resource for ViewStock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ViewStock extends ViewRecord
{
    protected static string $resource = StockResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_list')->label(__('common.back_to_list'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('index'))->tooltip(__('common.back_to_list_tooltip')), Actions\EditAction::make()->label(__('inventory.edit_stock_item')), Actions\Action::make('adjust_stock')->label(__('inventory.adjust_stock'))->icon('heroicon-o-adjustments-horizontal')->color('warning')->form([\Filament\Forms\Components\TextInput::make('quantity')->label(__('inventory.adjustment_quantity'))->numeric()->required()->helperText(__('inventory.adjustment_quantity_help')), \Filament\Forms\Components\Select::make('reason')->label(__('inventory.adjustment_reason'))->options(['manual_adjustment' => __('inventory.reason_manual_adjustment'), 'damage' => __('inventory.reason_damage'), 'theft' => __('inventory.reason_theft'), 'return' => __('inventory.reason_return'), 'restock' => __('inventory.reason_restock'), 'transfer' => __('inventory.reason_transfer')])->required(), \Filament\Forms\Components\Textarea::make('notes')->label(__('inventory.adjustment_notes'))->rows(3)])->action(function (array $data): void {
            $record = $this->getRecord();
            $record->adjustStock($data['quantity'], $data['reason']);
            Notification::make()->title(__('inventory.stock_adjusted'))->body(__('inventory.stock_adjusted_message', ['quantity' => $data['quantity'], 'product' => $record->display_name]))->success()->send();
        }), Actions\Action::make('reserve_stock')->label(__('inventory.reserve_stock'))->icon('heroicon-o-lock-closed')->color('info')->form([\Filament\Forms\Components\TextInput::make('quantity')->label(__('inventory.reserve_quantity'))->numeric()->required()->maxValue(fn(): int => $this->getRecord()->available_stock), \Filament\Forms\Components\Textarea::make('notes')->label(__('inventory.reserve_notes'))->rows(3)])->action(function (array $data): void {
            $record = $this->getRecord();
            if ($record->reserve($data['quantity'])) {
                Notification::make()->title(__('inventory.stock_reserved'))->body(__('inventory.stock_reserved_message', ['quantity' => $data['quantity'], 'product' => $record->display_name]))->success()->send();
            } else {
                Notification::make()->title(__('inventory.reserve_failed'))->body(__('inventory.reserve_failed_message'))->danger()->send();
            }
        }), Actions\Action::make('unreserve_stock')->label(__('inventory.unreserve_stock'))->icon('heroicon-o-lock-open')->color('gray')->visible(fn(): bool => $this->getRecord()->reserved > 0)->form([\Filament\Forms\Components\TextInput::make('quantity')->label(__('inventory.unreserve_quantity'))->numeric()->required()->maxValue(fn(): int => $this->getRecord()->reserved), \Filament\Forms\Components\Textarea::make('notes')->label(__('inventory.unreserve_notes'))->rows(3)])->action(function (array $data): void {
            $record = $this->getRecord();
            $record->unreserve($data['quantity']);
            Notification::make()->title(__('inventory.stock_unreserved'))->body(__('inventory.stock_unreserved_message', ['quantity' => $data['quantity'], 'product' => $record->display_name]))->success()->send();
        }), Actions\DeleteAction::make()->label(__('inventory.delete_stock_item'))];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [StockResource\Widgets\StockDetailsWidget::class, StockResource\Widgets\StockMovementsWidget::class];
    }
}