<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencyResource\Pages;

use App\Filament\Resources\SystemSettingDependencyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditSystemSettingDependency extends EditRecord
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
