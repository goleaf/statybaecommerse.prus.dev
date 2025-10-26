<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\Components\Quantity;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

final class InventoryManagement extends Page implements HasTable
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;
    use InteractsWithTable {
        InteractsWithTable::paginateTableQuery as protected basePaginateTableQuery;
    }

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations and communicates
     * the accepted union via PHPDoc for IDE support.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    public static function getNavigationGroup(): BackedEnum|string|null
    {
        return 'Products'; // Keep stock controls grouped with the rest of the product catalog tools.
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'inventory-management';
    }

    public function getTitle(): string
    {
        return 'Inventory Management';
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        $table = $table
            ->query(Product::query())
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('stock_quantity')->label('Stock')->numeric(),
            ])
            ->bulkActions([
                BulkAction::make('bulk_stock_update')
                    ->label('Bulk Stock Update')
                    ->form([
                        Select::make('operation')
                            ->options([
                                'increase' => 'Increase',
                                'decrease' => 'Decrease',
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

        return $this->applyToggleableTableLayout($table); // Reuse the helper to apply saved column visibility.
    }

    /**
     * Override the paginator so the generated links retain the current query string filters.
     */
    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        // Defer to the base Filament pagination logic before adjusting the query string behaviour.
        $paginator = $this->basePaginateTableQuery($query);

        // Append the existing request query parameters (product, location, etc.) to every page link.
        if (method_exists($paginator, 'withQueryString')) {
            return $paginator->withQueryString();
        }

        return $paginator;
    }
}
