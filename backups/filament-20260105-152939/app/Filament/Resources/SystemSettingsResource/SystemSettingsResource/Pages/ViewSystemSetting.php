<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingResource\Pages\ViewSystemSetting as BaseViewSystemSetting;
use App\Filament\Resources\SystemSettingsResource;

final class ViewSystemSetting extends BaseViewSystemSetting
{
    protected static string $resource = SystemSettingsResource::class;
}
