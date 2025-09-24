<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantInventoryResource\Pages;

use App\Filament\Resources\VariantInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVariantInventory extends EditRecord
{
    protected static string $resource = VariantInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
