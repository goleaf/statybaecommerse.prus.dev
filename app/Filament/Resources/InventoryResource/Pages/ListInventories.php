<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\InventoryResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListInventories extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('admin.inventory.actions.create')),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        /** @var Request $request */
        $request = request();

        if ($request->filled('product')) {
            $query->where('product_id', (int) $request->query('product'));
        }

        if ($request->filled('location')) {
            $query->where('location_id', (int) $request->query('location'));
        }

        if ($request->filled('is_tracked')) {
            $value = filter_var(
                $request->query('is_tracked'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );

            if ($value !== null) {
                $query->where('is_tracked', $value);
            }
        }

        if ($request->filled('stock_status')) {
            $status = (string) $request->query('stock_status');
            $query->when($status === 'out_of_stock', fn (Builder $builder) => $builder->whereRaw('quantity - reserved <= 0'))
                ->when(
                    $status === 'low_stock',
                    fn (Builder $builder) => $builder->whereRaw('quantity - reserved > 0 AND quantity - reserved <= threshold'),
                )
                ->when(
                    $status === 'in_stock',
                    fn (Builder $builder) => $builder->whereRaw('quantity - reserved > threshold'),
                );
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->whereHas('product', fn (Builder $productQuery): Builder => $productQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('location', fn (Builder $locationQuery): Builder => $locationQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('sort')) {
            $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
            $column = (string) $request->query('sort');
            $allowed = ['quantity', 'reserved', 'incoming', 'threshold', 'created_at'];

            if (in_array($column, $allowed, true)) {
                $query->reorder($column, $direction);
            }
        }

        return $this->applyWidgetTabFilters($query);
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('admin.inventory.tabs.all'))
                ->icon('heroicon-o-archive-box')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'in_stock' => WidgetTab::make(__('admin.inventory.tabs.in_stock'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved > threshold')),
            'low_stock' => WidgetTab::make(__('admin.inventory.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved <= threshold AND quantity - reserved > 0')),
            'out_of_stock' => WidgetTab::make(__('admin.inventory.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved <= 0')),
        ];
    }
}
