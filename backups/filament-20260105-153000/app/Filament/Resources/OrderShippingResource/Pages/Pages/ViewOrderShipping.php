<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippingResource\Pages;

use App\Filament\Resources\OrderShippingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderShipping extends ViewRecord
{
    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
