<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantStockHistoryResource\Pages;

use App\Filament\Resources\VariantStockHistoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateVariantStockHistory extends CreateRecord
{
    protected static string $resource = VariantStockHistoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['quantity_change'] = ($data['new_quantity'] ?? 0) - ($data['old_quantity'] ?? 0);

        return $data;
    }
}
