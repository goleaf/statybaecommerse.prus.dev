<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSimilarities extends ListRecords
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
