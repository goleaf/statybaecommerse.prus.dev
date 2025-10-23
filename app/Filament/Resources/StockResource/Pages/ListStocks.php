<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\StockResource;
use Filament\Actions;

class ListStocks extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
