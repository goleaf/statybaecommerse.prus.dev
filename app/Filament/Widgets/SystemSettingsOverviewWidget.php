<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class SystemSettingsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSettings = SystemSetting::count();
        $activeSettings = SystemSetting::where('is_active', true)->count();
        $publicSettings = SystemSetting::where('is_public', true)->where('is_active', true)->count();
        $encryptedSettings = SystemSetting::where('is_encrypted', true)->where('is_active', true)->count();
        $categoriesCount = SystemSettingCategory::where('is_active', true)->count();

        return [
            Stat::make(__('admin.system_settings.total_settings'), $totalSettings)
                ->description(__('admin.system_settings.total_settings_description'))
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('primary'),

            Stat::make(__('admin.system_settings.active_settings'), $activeSettings)
                ->description(__('admin.system_settings.active_settings_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.system_settings.public_settings'), $publicSettings)
                ->description(__('admin.system_settings.public_settings_description'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make(__('admin.system_settings.encrypted_settings'), $encryptedSettings)
                ->description(__('admin.system_settings.encrypted_settings_description'))
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),

            Stat::make(__('admin.system_settings.categories'), $categoriesCount)
                ->description(__('admin.system_settings.categories_description'))
                ->descriptionIcon('heroicon-m-folder')
                ->color('secondary'),
        ];
    }
}