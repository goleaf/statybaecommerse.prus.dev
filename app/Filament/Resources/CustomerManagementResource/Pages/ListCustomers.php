<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CustomerManagementResource;
use Filament\Actions;

final class ListCustomers extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
