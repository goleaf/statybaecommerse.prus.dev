<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderShippings\OrderShippingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderShippings extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = OrderShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
