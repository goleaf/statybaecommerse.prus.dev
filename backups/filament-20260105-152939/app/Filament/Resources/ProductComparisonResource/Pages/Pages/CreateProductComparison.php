<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisonResource\Pages;

use App\Filament\Resources\ProductComparisonResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductComparison extends CreateRecord
{
    protected static string $resource = ProductComparisonResource::class;
}
