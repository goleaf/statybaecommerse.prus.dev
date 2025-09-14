<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductVariant extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;
}
