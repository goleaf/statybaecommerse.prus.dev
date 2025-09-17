<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class SystemSettingsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSettings = SystemSetting::count();
        $activeSettings = SystemSetting::where('is_active', true)->count();
        $publicSettings = SystemSetting::where('is_public', true)->count();
        $encryptedSettings = SystemSetting::where('is_encrypted', true)->count();

        return [
            Stat::make(__('system_settings.stats.total_settings'), $totalSettings)
                ->description(__('system_settings.stats.total_settings_description'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('primary'),

            Stat::make(__('system_settings.stats.active_settings'), $activeSettings)
                ->description(__('system_settings.stats.active_settings_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('system_settings.stats.public_settings'), $publicSettings)
                ->description(__('system_settings.stats.public_settings_description'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make(__('system_settings.stats.encrypted_settings'), $encryptedSettings)
                ->description(__('system_settings.stats.encrypted_settings_description'))
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),
        ];
    }
}
