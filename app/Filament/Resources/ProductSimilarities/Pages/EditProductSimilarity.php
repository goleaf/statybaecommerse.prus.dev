<?php

namespace App\Filament\Resources\ProductSimilarities\Pages;

use App\Filament\Resources\ProductSimilarities\ProductSimilarityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductSimilarity extends EditRecord
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
