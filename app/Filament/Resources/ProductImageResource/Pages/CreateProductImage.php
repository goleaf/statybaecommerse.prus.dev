<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductImage extends CreateRecord
{
    protected static string $resource = ProductImageResource::class;
}
