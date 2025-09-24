<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSettingTranslation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SystemSettingTranslationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.system_setting_translations.total_translations'), SystemSettingTranslation::count())
                ->description(__('admin.system_setting_translations.total_translations_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make(__('admin.system_setting_translations.active_translations'), SystemSettingTranslation::where('is_active', true)->count())
                ->description(__('admin.system_setting_translations.active_translations_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.system_setting_translations.public_translations'), SystemSettingTranslation::where('is_public', true)->count())
                ->description(__('admin.system_setting_translations.public_translations_description'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make(__('admin.system_setting_translations.languages_count'), SystemSettingTranslation::distinct('locale')->count('locale'))
                ->description(__('admin.system_setting_translations.languages_count_description'))
                ->descriptionIcon('heroicon-m-language')
                ->color('warning'),
        ];
    }
}
