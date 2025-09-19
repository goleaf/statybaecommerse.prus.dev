<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateVariantPriceHistory extends CreateRecord
{
    protected static string $resource = VariantPriceHistoryResource::class;
}

