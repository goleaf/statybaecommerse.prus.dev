<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSimilarity extends EditRecord
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['calculated_at'] = now();

        return $data;
    }
}
