<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\OrderItemResource;
use Filament\Actions;

final class ListOrderItems extends BaseListRecords
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
