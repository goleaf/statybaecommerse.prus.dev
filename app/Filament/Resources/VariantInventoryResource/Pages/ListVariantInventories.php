<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantInventoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantInventoryResource;
use Filament\Actions\CreateAction;

class ListVariantInventories extends BaseListRecords
{
    
    protected static string $resource = VariantInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
