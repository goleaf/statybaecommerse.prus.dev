<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use UnitEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use UnitEnum;

final class InventoryManagement extends Page implements HasTable
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static UnitEnum|string|null $navigationGroup = 'Products';

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

        return $this->applyToggleableTableLayout($table);
    }
}
