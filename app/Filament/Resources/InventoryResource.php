<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\Schemas\InventoryForm;
use App\Filament\Resources\InventoryResource\Schemas\InventoryInfolist;
use App\Models\Inventory;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class InventoryResource extends BaseResource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'inventory-management';

    public static function getNavigationLabel(): string
    {
        return __('admin.inventory_management.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.inventory_management.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.inventory.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved_quantity')
                    ->label(__('admin.inventory.reserved_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('admin.inventory.available_quantity'))
                    ->getStateUsing(fn ($record) => $record->quantity - $record->reserved_quantity)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label(__('admin.inventory.low_stock_threshold'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.inventory.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                BulkAction::make('bulk_stock_update')
                    ->label(__('admin.inventory_management.bulk_stock_update.label'))
                    ->form([
                        \Filament\Forms\Components\Select::make('operation')
                            ->label(__('admin.inventory_management.bulk_stock_update.operation'))
                            ->options([
                                'increase' => __('admin.inventory_management.bulk_stock_update.increase'),
                                'decrease' => __('admin.inventory_management.bulk_stock_update.decrease'),
                            ])
                            ->required(),
                        \App\Filament\Forms\Components\Quantity::make('quantity')
                            ->minValue(0)
                            ->steps(1)
                            ->default(0)
                            ->required(),
                    ])
                    ->action(function (array $data, $records): void {
                        foreach ($records as $inventory) {
                            if (! $inventory instanceof Inventory) {
                                continue;
                            }

                            $delta = (int) ($data['quantity'] ?? 0);

                            if (($data['operation'] ?? 'increase') === 'decrease') {
                                $delta = -$delta;
                            }

                            $inventory->qty = max(0, (int) $inventory->qty + $delta);
                            $inventory->save();
                        }
                    }),
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view'   => Pages\ViewInventory::route('/{record}'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
