<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EnumManagementResource;
use Filament\Actions;

final class ListEnumManagement extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EnumManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
