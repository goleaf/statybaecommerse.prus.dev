<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencyResource\Pages;

use App\Filament\Resources\SystemSettingDependencyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewSystemSettingDependency extends ViewRecord
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
