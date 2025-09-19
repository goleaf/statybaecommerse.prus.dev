<?php

namespace App\Filament\Resources\SystemSettingDependencies\Pages;

use App\Filament\Resources\SystemSettingDependencies\SystemSettingDependencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSystemSettingDependency extends EditRecord
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
