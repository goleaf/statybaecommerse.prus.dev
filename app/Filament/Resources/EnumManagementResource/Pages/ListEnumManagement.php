<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListEnumManagement extends ListRecords
{
    protected static string $resource = EnumManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
