<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListOrderItems extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
