<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
}
