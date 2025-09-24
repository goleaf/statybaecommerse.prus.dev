<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewEnumManagement extends ViewRecord
{
    protected static string $resource = EnumManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
