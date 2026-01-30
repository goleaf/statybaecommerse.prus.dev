<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProductVariants extends ViewRecord
{
    protected static string $resource = ProductVariantResource::class;
}
