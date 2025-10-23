<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CustomerGroupResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCustomerGroups extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CustomerGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }
}
