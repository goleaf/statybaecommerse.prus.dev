<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptions\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ShippingOptions\ShippingOptionResource;
use Filament\Actions\CreateAction;

class ListShippingOptions extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ShippingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
