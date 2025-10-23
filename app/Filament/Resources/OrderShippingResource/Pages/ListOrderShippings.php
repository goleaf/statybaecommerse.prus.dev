<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippingResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderShippingResource;
use Filament\Actions;

class ListOrderShippings extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
