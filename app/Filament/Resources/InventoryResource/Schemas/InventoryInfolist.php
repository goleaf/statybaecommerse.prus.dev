<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.inventory.basic_information'))
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('product.name')
                                ->label(__('messages.product')),
                            TextEntry::make('product.sku')
                                ->label(__('messages.sku')),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('quantity')
                                ->label(__('messages.quantity')),
                            TextEntry::make('reserved')
                                ->label(__('admin.inventory.reserved_quantity')),
                            TextEntry::make('available_quantity')
                                ->label(__('admin.inventory.available_quantity'))
                                ->getStateUsing(static fn ($record): int => max(0, (int) $record->quantity - (int) $record->reserved)),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('threshold')
                                ->label(__('admin.inventory.low_stock_threshold')),
                            TextEntry::make('updated_at')
                                ->label(__('admin.inventory.updated_at'))
                                ->dateTime(),
                        ]),
                ]),
        ]);
    }
}
