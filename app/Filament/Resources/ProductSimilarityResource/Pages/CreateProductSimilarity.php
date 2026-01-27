<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductSimilarity extends CreateRecord
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ListAction::make(),
        ];
    }
}
