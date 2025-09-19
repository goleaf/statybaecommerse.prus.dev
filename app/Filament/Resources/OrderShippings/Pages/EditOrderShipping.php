<?php

namespace App\Filament\Resources\OrderShippings\Pages;

use App\Filament\Resources\OrderShippings\OrderShippingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderShipping extends EditRecord
{
    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
