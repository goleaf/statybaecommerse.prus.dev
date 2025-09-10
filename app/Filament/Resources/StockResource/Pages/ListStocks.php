<?php declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Resources\Pages\ListRecords;

final class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;
}


