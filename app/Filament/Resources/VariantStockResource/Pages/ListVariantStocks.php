<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantStockResource\Pages;

use App\Filament\Resources\VariantStockResource;
use App\Filament\Pages\Support\BaseListRecords;

final class ListVariantStocks extends BaseListRecords
{
    protected static string $resource = VariantStockResource::class;
}
