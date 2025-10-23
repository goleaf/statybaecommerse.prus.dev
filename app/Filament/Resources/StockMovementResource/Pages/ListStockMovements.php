<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\StockMovementResource;
use Filament\Actions;

final class ListStockMovements extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
