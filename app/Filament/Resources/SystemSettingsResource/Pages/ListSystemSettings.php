<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListSystemSettings extends ListRecords
{
    protected static string $resource = SystemSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
