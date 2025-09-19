<?php declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.inventory.tabs.all'))
                ->icon('heroicon-o-archive-box'),
            'in_stock' => Tab::make(__('admin.inventory.tabs.in_stock'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved > threshold')),
            'low_stock' => Tab::make(__('admin.inventory.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved <= threshold AND quantity - reserved > 0')),
            'out_of_stock' => Tab::make(__('admin.inventory.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved <= 0')),
        ];
    }
}
