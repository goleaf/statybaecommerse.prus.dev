<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSimilarity extends ViewRecord
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
