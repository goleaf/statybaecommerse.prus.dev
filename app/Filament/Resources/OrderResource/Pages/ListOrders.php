<?php

declare (strict_types=1);
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListOrders
 * 
 * Filament v4 resource for ListOrders management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make('orders.tabs.all')->badge(fn() => $this->getModel()::count()), 'pending' => Tab::make('orders.tabs.pending')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))->badge(fn() => $this->getModel()::where('status', 'pending')->count()), 'processing' => Tab::make('orders.tabs.processing')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'processing'))->badge(fn() => $this->getModel()::where('status', 'processing')->count()), 'shipped' => Tab::make('orders.tabs.shipped')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'shipped'))->badge(fn() => $this->getModel()::where('status', 'shipped')->count()), 'delivered' => Tab::make('orders.tabs.delivered')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'delivered'))->badge(fn() => $this->getModel()::where('status', 'delivered')->count()), 'completed' => Tab::make('orders.tabs.completed')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed'))->badge(fn() => $this->getModel()::where('status', 'completed')->count()), 'cancelled' => Tab::make('orders.tabs.cancelled')->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled'))->badge(fn() => $this->getModel()::where('status', 'cancelled')->count())];
    }
}