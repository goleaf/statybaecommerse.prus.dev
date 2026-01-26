<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\Components\Quantity;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

final class InventoryManagement extends Page implements HasTable
{
    use InteractsWithTable {
        paginateTableQuery as basePaginateTableQuery;
    }


    protected string $view = 'filament.pages.inventory-management';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.admin);
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'inventory-management';
    }

    public function getTitle(): string
    {
        return __('admin.inventory_management.title');
    }

    public function table(Table $table): Table
    {
        $table = $table
            ->query(Product::query())
            ->columns([
                TextColumn::make('name')->label(__('admin.inventory_management.columns.name'))->searchable(),
                TextColumn::make('stock_quantity')->label(__('admin.inventory_management.columns.stock'))->numeric(),
            ])
            ->bulkActions([
                BulkAction::make('bulk_stock_update')
                    ->label(__('admin.inventory_management.bulk_stock_update.label'))
                    ->form([
                        Select::make('operation')
                            ->label(__('admin.inventory_management.bulk_stock_update.operation'))
                            ->options([
                                'increase' => __('admin.inventory_management.bulk_stock_update.increase'),
                                'decrease' => __('admin.inventory_management.bulk_stock_update.decrease'),
                            ])
                            ->required(),
                        Quantity::make('quantity')
                            ->minValue(0)
                            ->steps(1)
                            ->default(0)
                            ->required(),
                    ])
                    ->action(function (array $data, $records): void {
                        foreach ($records as $product) {
                            if (! $product instanceof Product) {
                                continue;
                            }

                            $delta = (int) ($data['quantity'] ?? 0);

                            if (($data['operation'] ?? 'increase') === 'decrease') {
                                $delta = -$delta;
                            }

                            $product->stock_quantity = max(0, (int) $product->stock_quantity + $delta);
                            $product->save();
                        }
                    }),
            ]);

        return $table;
    }

    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        $paginator = $this->basePaginateTableQuery($query);

        if (method_exists($paginator, 'withQueryString')) {
            return $paginator->withQueryString();
        }

        return $paginator;
    }
}
