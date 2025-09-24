<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantInventoryResource\Pages;

use App\Filament\Resources\VariantInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVariantInventories extends ListRecords
{
    protected static string $resource = VariantInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
