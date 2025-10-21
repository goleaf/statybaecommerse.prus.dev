<?php

namespace App\Filament\Resources\ProductSimilarities\Pages;

use App\Filament\Resources\ProductSimilarities\ProductSimilarityResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListProductSimilarities extends BaseListRecords
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
