<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantStockResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantStockResource;

final class ListVariantStocks extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantStockResource::class;
}
