<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ProductSimilarityResource;
use Filament\Actions;

class ListProductSimilarities extends BaseListRecords
{
    
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
