<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListOrders extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('orders', 'create')),
        ];
    }
}
