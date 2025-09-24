<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Resources\PriceListItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePriceListItem extends CreateRecord
{
    protected static string $resource = PriceListItemResource::class;
}
