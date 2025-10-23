<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductSimilarities\ProductSimilarityResource;
use Filament\Actions\CreateAction;

class ListProductSimilarities extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
