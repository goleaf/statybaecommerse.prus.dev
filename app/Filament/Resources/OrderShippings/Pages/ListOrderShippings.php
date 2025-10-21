<?php

namespace App\Filament\Resources\OrderShippings\Pages;

use App\Filament\Resources\OrderShippings\OrderShippingResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListOrderShippings extends BaseListRecords
{
    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
