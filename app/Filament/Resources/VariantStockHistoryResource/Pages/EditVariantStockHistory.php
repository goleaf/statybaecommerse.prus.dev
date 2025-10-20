<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantStockHistoryResource\Pages;

use App\Filament\Resources\VariantStockHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditVariantStockHistory extends EditRecord
{
    protected static string $resource = VariantStockHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['quantity_change'] = ($data['new_quantity'] ?? 0) - ($data['old_quantity'] ?? 0);

        return $data;
    }
}
