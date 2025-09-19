<?php

namespace App\Filament\Resources\SystemSettingDependencies\Pages;

use App\Filament\Resources\SystemSettingDependencies\SystemSettingDependencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSystemSettingDependencies extends ListRecords
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
