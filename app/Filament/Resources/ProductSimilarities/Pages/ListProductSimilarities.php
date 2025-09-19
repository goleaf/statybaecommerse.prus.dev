<?php

namespace App\Filament\Resources\ProductSimilarities\Pages;

use App\Filament\Resources\ProductSimilarities\ProductSimilarityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductSimilarities extends ListRecords
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
