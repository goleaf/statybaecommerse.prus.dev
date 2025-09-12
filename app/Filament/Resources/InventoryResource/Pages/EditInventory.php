<?php declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
