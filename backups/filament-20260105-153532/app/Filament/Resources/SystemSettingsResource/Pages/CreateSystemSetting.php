<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingResource\Pages\CreateSystemSetting as BaseCreateSystemSetting;
use App\Filament\Resources\SystemSettingsResource;

final class CreateSystemSetting extends BaseCreateSystemSetting
{
    protected static string $resource = SystemSettingsResource::class;
}
