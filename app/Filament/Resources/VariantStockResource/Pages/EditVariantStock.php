<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantStockResource\Pages;

use App\Filament\Resources\VariantStockResource;
use Filament\Resources\Pages\EditRecord;

final class EditVariantStock extends EditRecord
{
    protected static string $resource = VariantStockResource::class;
}
