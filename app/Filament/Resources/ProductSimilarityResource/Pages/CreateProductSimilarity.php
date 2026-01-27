<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSimilarity extends CreateRecord
{
    protected static string $resource = ProductSimilarityResource::class;
}
