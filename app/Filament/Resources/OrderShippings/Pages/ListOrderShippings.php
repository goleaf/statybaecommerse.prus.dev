<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\OrderShippings\OrderShippingResource;
use Filament\Actions\CreateAction;

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
