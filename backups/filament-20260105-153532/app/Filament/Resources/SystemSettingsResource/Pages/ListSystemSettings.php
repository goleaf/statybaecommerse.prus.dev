<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings as BaseListSystemSettings;
use App\Filament\Resources\SystemSettingsResource;

final class ListSystemSettings extends BaseListSystemSettings
{
    protected static string $resource = SystemSettingsResource::class;
}
