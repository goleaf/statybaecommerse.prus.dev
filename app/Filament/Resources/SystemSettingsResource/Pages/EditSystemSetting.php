<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingResource\Pages\EditSystemSetting as BaseEditSystemSetting;
use App\Filament\Resources\SystemSettingsResource;

final class EditSystemSetting extends BaseEditSystemSetting
{
    protected static string $resource = SystemSettingsResource::class;
}
