<?php

namespace App\Filament\Resources\OrderShippings\Pages;

use App\Filament\Resources\OrderShippings\OrderShippingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderShippings extends ListRecords
{
    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
